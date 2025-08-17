<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DrugInteractionService
{
    // Interaction severity levels
    const SEVERITY_MINOR = 'minor';
    const SEVERITY_MODERATE = 'moderate';
    const SEVERITY_MAJOR = 'major';
    const SEVERITY_CONTRAINDICATED = 'contraindicated';

    /**
     * Check for interactions between multiple medications.
     */
    public function checkInteractions(array $productIds): array
    {
        $products = Product::whereIn('id', $productIds)
            ->active()
            ->get();

        if ($products->count() < 2) {
            return [
                'has_interactions' => false,
                'interaction_count' => 0,
                'interactions' => [],
                'summary' => 'Need at least 2 medications to check for interactions'
            ];
        }

        $interactions = [];
        $checkedPairs = [];

        foreach ($products as $product1) {
            foreach ($products as $product2) {
                if ($product1->id === $product2->id) {
                    continue;
                }

                // Avoid checking the same pair twice
                $pairKey = min($product1->id, $product2->id) . '-' . max($product1->id, $product2->id);
                if (in_array($pairKey, $checkedPairs)) {
                    continue;
                }
                $checkedPairs[] = $pairKey;

                $interaction = $this->checkPairInteraction($product1, $product2);
                if ($interaction) {
                    $interactions[] = $interaction;
                }
            }
        }

        return [
            'has_interactions' => !empty($interactions),
            'interaction_count' => count($interactions),
            'interactions' => $interactions,
            'summary' => $this->generateInteractionSummary($interactions),
            'highest_severity' => $this->getHighestSeverity($interactions),
            'recommendations' => $this->generateRecommendations($interactions)
        ];
    }

    /**
     * Check interaction between two specific products.
     */
    public function checkPairInteraction(Product $product1, Product $product2): ?array
    {
        // Check direct drug interactions stored in the product
        $directInteraction = $this->checkDirectInteraction($product1, $product2);
        if ($directInteraction) {
            return $directInteraction;
        }

        // Check active ingredient interactions
        $ingredientInteraction = $this->checkActiveIngredientInteractions($product1, $product2);
        if ($ingredientInteraction) {
            return $ingredientInteraction;
        }

        // Check therapeutic class interactions
        $classInteraction = $this->checkTherapeuticClassInteractions($product1, $product2);
        if ($classInteraction) {
            return $classInteraction;
        }

        // Check for contraindications
        $contraindicationInteraction = $this->checkContraindications($product1, $product2);
        if ($contraindicationInteraction) {
            return $contraindicationInteraction;
        }

        return null;
    }

    /**
     * Get comprehensive drug profile with safety information.
     */
    public function getDrugProfile(Product $product): array
    {
        return [
            'basic_info' => [
                'id' => $product->id,
                'name' => $product->getDisplayName(),
                'generic_name' => $product->generic_name,
                'brand_name' => $product->brand_name,
                'ndc_number' => $product->ndc_number,
                'strength' => $product->strength,
                'dosage_form' => $product->dosage_form
            ],
            'safety_info' => [
                'dea_schedule' => $product->dea_schedule,
                'dea_schedule_description' => $product->dea_schedule_description,
                'is_controlled_substance' => $product->is_controlled_substance,
                'requires_prescription' => $product->requires_prescription,
                'is_otc' => $product->is_otc
            ],
            'clinical_info' => [
                'active_ingredients' => $product->getActiveIngredientsList(),
                'therapeutic_class' => $product->category->therapeutic_class ?? null,
                'warnings' => $product->warnings,
                'side_effects' => $product->side_effects,
                'contraindications' => $product->contraindications,
                'drug_interactions' => $product->drug_interactions ?? [],
                'dosage_instructions' => $product->dosage_instructions
            ],
            'storage_info' => [
                'storage_requirements' => $product->storage_requirements,
                'temperature_range' => [
                    'min' => $product->storage_temperature_min,
                    'max' => $product->storage_temperature_max
                ],
                'expiration_date' => $product->expiration_date?->format('Y-m-d'),
                'is_expired' => $product->is_expired,
                'is_expiring_soon' => $product->is_expiring_soon
            ]
        ];
    }

    /**
     * Get all known interactions for a specific medication.
     */
    public function getKnownInteractions(Product $product): array
    {
        $interactions = [];
        
        // Get stored interactions
        $storedInteractions = $product->drug_interactions ?? [];
        foreach ($storedInteractions as $interactionDrug) {
            $interactions[] = [
                'drug_name' => $interactionDrug,
                'severity' => self::SEVERITY_MODERATE, // Default severity
                'description' => "May interact with {$interactionDrug}",
                'source' => 'product_data'
            ];
        }

        // Find products that list this medication as an interaction
        $interactingProducts = Product::where('drug_interactions', 'like', "%{$product->generic_name}%")
            ->orWhereJsonContains('drug_interactions', $product->generic_name)
            ->where('id', '!=', $product->id)
            ->active()
            ->get();

        foreach ($interactingProducts as $interactingProduct) {
            $interactions[] = [
                'drug_name' => $interactingProduct->getDisplayName(),
                'drug_id' => $interactingProduct->id,
                'severity' => self::SEVERITY_MODERATE,
                'description' => "Known interaction with {$interactingProduct->getDisplayName()}",
                'source' => 'cross_reference'
            ];
        }

        return [
            'product' => $product->getDisplayName(),
            'total_interactions' => count($interactions),
            'interactions' => $interactions,
            'last_updated' => now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Check for duplicate therapy (same active ingredient).
     */
    public function checkDuplicateTherapy(array $productIds): array
    {
        $products = Product::whereIn('id', $productIds)->active()->get();
        $duplicates = [];
        $ingredientGroups = [];

        foreach ($products as $product) {
            $ingredients = $product->getActiveIngredientsList();
            
            foreach ($ingredients as $ingredient) {
                $normalizedIngredient = strtolower(trim($ingredient));
                
                if (!isset($ingredientGroups[$normalizedIngredient])) {
                    $ingredientGroups[$normalizedIngredient] = [];
                }
                
                $ingredientGroups[$normalizedIngredient][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->getDisplayName(),
                    'strength' => $product->strength
                ];
            }
        }

        // Find duplicates
        foreach ($ingredientGroups as $ingredient => $products) {
            if (count($products) > 1) {
                $duplicates[] = [
                    'active_ingredient' => $ingredient,
                    'products' => $products,
                    'severity' => self::SEVERITY_MAJOR,
                    'warning' => 'Duplicate therapy detected - same active ingredient in multiple medications'
                ];
            }
        }

        return [
            'has_duplicates' => !empty($duplicates),
            'duplicate_count' => count($duplicates),
            'duplicates' => $duplicates
        ];
    }

    /**
     * Generate safety alerts for a medication regimen.
     */
    public function generateSafetyAlerts(array $productIds): array
    {
        $products = Product::whereIn('id', $productIds)->active()->get();
        $alerts = [];

        // Check for controlled substances
        $controlledSubstances = $products->where('is_controlled_substance', true);
        if ($controlledSubstances->isNotEmpty()) {
            $alerts[] = [
                'type' => 'controlled_substance',
                'severity' => self::SEVERITY_MAJOR,
                'title' => 'Controlled Substances Detected',
                'message' => 'This regimen includes ' . $controlledSubstances->count() . ' controlled substance(s). Extra monitoring required.',
                'products' => $controlledSubstances->pluck('name')->toArray()
            ];
        }

        // Check for high-alert medications
        $highAlertDrugs = $this->getHighAlertMedications($products);
        if (!empty($highAlertDrugs)) {
            $alerts[] = [
                'type' => 'high_alert',
                'severity' => self::SEVERITY_MAJOR,
                'title' => 'High-Alert Medications',
                'message' => 'This regimen includes high-alert medications that require special attention.',
                'products' => $highAlertDrugs
            ];
        }

        // Check for age-related warnings
        $geriatricWarnings = $this->checkGeriatricWarnings($products);
        if (!empty($geriatricWarnings)) {
            $alerts = array_merge($alerts, $geriatricWarnings);
        }

        // Check for pregnancy warnings
        $pregnancyWarnings = $this->checkPregnancyWarnings($products);
        if (!empty($pregnancyWarnings)) {
            $alerts = array_merge($alerts, $pregnancyWarnings);
        }

        return [
            'alert_count' => count($alerts),
            'highest_severity' => $this->getHighestSeverity($alerts),
            'alerts' => $alerts
        ];
    }

    /**
     * Private helper methods
     */
    private function checkDirectInteraction(Product $product1, Product $product2): ?array
    {
        $interactions1 = $product1->drug_interactions ?? [];
        $interactions2 = $product2->drug_interactions ?? [];

        // Check if product1 lists product2 as an interaction
        if (in_array($product2->generic_name, $interactions1) || 
            $this->arrayContainsPartial($interactions1, $product2->generic_name)) {
            return [
                'type' => 'direct_interaction',
                'drug1' => [
                    'id' => $product1->id,
                    'name' => $product1->getDisplayName()
                ],
                'drug2' => [
                    'id' => $product2->id,
                    'name' => $product2->getDisplayName()
                ],
                'severity' => self::SEVERITY_MODERATE,
                'description' => "Direct interaction documented between {$product1->getDisplayName()} and {$product2->getDisplayName()}",
                'recommendation' => 'Monitor patient closely for adverse effects'
            ];
        }

        // Check if product2 lists product1 as an interaction
        if (in_array($product1->generic_name, $interactions2) || 
            $this->arrayContainsPartial($interactions2, $product1->generic_name)) {
            return [
                'type' => 'direct_interaction',
                'drug1' => [
                    'id' => $product1->id,
                    'name' => $product1->getDisplayName()
                ],
                'drug2' => [
                    'id' => $product2->id,
                    'name' => $product2->getDisplayName()
                ],
                'severity' => self::SEVERITY_MODERATE,
                'description' => "Direct interaction documented between {$product1->getDisplayName()} and {$product2->getDisplayName()}",
                'recommendation' => 'Monitor patient closely for adverse effects'
            ];
        }

        return null;
    }

    private function checkActiveIngredientInteractions(Product $product1, Product $product2): ?array
    {
        $ingredients1 = $product1->getActiveIngredientsList();
        $ingredients2 = $product2->getActiveIngredientsList();

        // Check for same active ingredients (duplicate therapy)
        $commonIngredients = array_intersect(
            array_map('strtolower', $ingredients1),
            array_map('strtolower', $ingredients2)
        );

        if (!empty($commonIngredients)) {
            return [
                'type' => 'duplicate_therapy',
                'drug1' => [
                    'id' => $product1->id,
                    'name' => $product1->getDisplayName()
                ],
                'drug2' => [
                    'id' => $product2->id,
                    'name' => $product2->getDisplayName()
                ],
                'severity' => self::SEVERITY_MAJOR,
                'description' => "Duplicate therapy - both medications contain: " . implode(', ', $commonIngredients),
                'recommendation' => 'Consider discontinuing one medication or adjusting dosages'
            ];
        }

        return null;
    }

    private function checkTherapeuticClassInteractions(Product $product1, Product $product2): ?array
    {
        $class1 = $product1->category->therapeutic_class ?? null;
        $class2 = $product2->category->therapeutic_class ?? null;

        if (!$class1 || !$class2) {
            return null;
        }

        // Define known interacting therapeutic classes
        $interactingClasses = [
            'Cardiovascular' => ['CNS', 'Respiratory'],
            'CNS' => ['Cardiovascular', 'Respiratory'],
            'Respiratory' => ['Cardiovascular', 'CNS']
        ];

        if (isset($interactingClasses[$class1]) && in_array($class2, $interactingClasses[$class1])) {
            return [
                'type' => 'therapeutic_class',
                'drug1' => [
                    'id' => $product1->id,
                    'name' => $product1->getDisplayName()
                ],
                'drug2' => [
                    'id' => $product2->id,
                    'name' => $product2->getDisplayName()
                ],
                'severity' => self::SEVERITY_MINOR,
                'description' => "Potential interaction between {$class1} and {$class2} therapeutic classes",
                'recommendation' => 'Monitor for additive effects'
            ];
        }

        return null;
    }

    private function checkContraindications(Product $product1, Product $product2): ?array
    {
        $contraindications1 = $product1->contraindications;
        $contraindications2 = $product2->contraindications;

        if (!$contraindications1 || !$contraindications2) {
            return null;
        }

        // Simple keyword matching - in a real system, this would be more sophisticated
        $keywords1 = explode(' ', strtolower($contraindications1));
        $keywords2 = explode(' ', strtolower($contraindications2));

        $commonKeywords = array_intersect($keywords1, $keywords2);
        $significantKeywords = array_filter($commonKeywords, function($keyword) {
            return strlen($keyword) > 4; // Only consider longer words
        });

        if (!empty($significantKeywords)) {
            return [
                'type' => 'contraindication',
                'drug1' => [
                    'id' => $product1->id,
                    'name' => $product1->getDisplayName()
                ],
                'drug2' => [
                    'id' => $product2->id,
                    'name' => $product2->getDisplayName()
                ],
                'severity' => self::SEVERITY_MODERATE,
                'description' => 'Potential contraindication based on shared contraindication keywords',
                'recommendation' => 'Review contraindications carefully before co-administration'
            ];
        }

        return null;
    }

    private function arrayContainsPartial(array $array, string $needle): bool
    {
        foreach ($array as $item) {
            if (stripos($item, $needle) !== false || stripos($needle, $item) !== false) {
                return true;
            }
        }
        return false;
    }

    private function generateInteractionSummary(array $interactions): string
    {
        if (empty($interactions)) {
            return 'No interactions detected';
        }

        $severityCount = array_count_values(array_column($interactions, 'severity'));
        $parts = [];

        if (!empty($severityCount[self::SEVERITY_CONTRAINDICATED])) {
            $parts[] = $severityCount[self::SEVERITY_CONTRAINDICATED] . ' contraindicated';
        }
        if (!empty($severityCount[self::SEVERITY_MAJOR])) {
            $parts[] = $severityCount[self::SEVERITY_MAJOR] . ' major';
        }
        if (!empty($severityCount[self::SEVERITY_MODERATE])) {
            $parts[] = $severityCount[self::SEVERITY_MODERATE] . ' moderate';
        }
        if (!empty($severityCount[self::SEVERITY_MINOR])) {
            $parts[] = $severityCount[self::SEVERITY_MINOR] . ' minor';
        }

        return 'Found ' . implode(', ', $parts) . ' interaction(s)';
    }

    private function getHighestSeverity(array $items): string
    {
        $severities = array_column($items, 'severity');
        
        if (in_array(self::SEVERITY_CONTRAINDICATED, $severities)) {
            return self::SEVERITY_CONTRAINDICATED;
        }
        if (in_array(self::SEVERITY_MAJOR, $severities)) {
            return self::SEVERITY_MAJOR;
        }
        if (in_array(self::SEVERITY_MODERATE, $severities)) {
            return self::SEVERITY_MODERATE;
        }
        if (in_array(self::SEVERITY_MINOR, $severities)) {
            return self::SEVERITY_MINOR;
        }
        
        return self::SEVERITY_MINOR;
    }

    private function generateRecommendations(array $interactions): array
    {
        $recommendations = [];
        
        foreach ($interactions as $interaction) {
            if (isset($interaction['recommendation'])) {
                $recommendations[] = $interaction['recommendation'];
            }
        }

        // Add general recommendations based on severity
        $highestSeverity = $this->getHighestSeverity($interactions);
        
        switch ($highestSeverity) {
            case self::SEVERITY_CONTRAINDICATED:
                $recommendations[] = 'Consider alternative medications - contraindicated combination detected';
                break;
            case self::SEVERITY_MAJOR:
                $recommendations[] = 'Closely monitor patient and consider dose adjustments';
                break;
            case self::SEVERITY_MODERATE:
                $recommendations[] = 'Monitor patient for signs of interaction';
                break;
        }

        return array_unique($recommendations);
    }

    private function getHighAlertMedications(Collection $products): array
    {
        $highAlertKeywords = ['warfarin', 'insulin', 'heparin', 'morphine', 'fentanyl'];
        $highAlertDrugs = [];

        foreach ($products as $product) {
            foreach ($highAlertKeywords as $keyword) {
                if (stripos($product->generic_name, $keyword) !== false || 
                    stripos($product->name, $keyword) !== false) {
                    $highAlertDrugs[] = $product->getDisplayName();
                    break;
                }
            }
        }

        return $highAlertDrugs;
    }

    private function checkGeriatricWarnings(Collection $products): array
    {
        $warnings = [];
        $geriatricKeywords = ['anticholinergic', 'benzodiazepine', 'barbiturate'];

        foreach ($products as $product) {
            foreach ($geriatricKeywords as $keyword) {
                if (stripos($product->description, $keyword) !== false || 
                    stripos($product->warnings, $keyword) !== false) {
                    $warnings[] = [
                        'type' => 'geriatric_warning',
                        'severity' => self::SEVERITY_MODERATE,
                        'title' => 'Geriatric Caution',
                        'message' => "{$product->getDisplayName()} requires special caution in elderly patients",
                        'product' => $product->getDisplayName()
                    ];
                    break;
                }
            }
        }

        return $warnings;
    }

    private function checkPregnancyWarnings(Collection $products): array
    {
        $warnings = [];
        $pregnancyKeywords = ['pregnancy', 'teratogenic', 'fetal'];

        foreach ($products as $product) {
            foreach ($pregnancyKeywords as $keyword) {
                if (stripos($product->warnings, $keyword) !== false || 
                    stripos($product->contraindications, $keyword) !== false) {
                    $warnings[] = [
                        'type' => 'pregnancy_warning',
                        'severity' => self::SEVERITY_MAJOR,
                        'title' => 'Pregnancy Warning',
                        'message' => "{$product->getDisplayName()} may not be safe during pregnancy",
                        'product' => $product->getDisplayName()
                    ];
                    break;
                }
            }
        }

        return $warnings;
    }
}