<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MedicationSearchService
{
    /**
     * Advanced search with multiple criteria and scoring.
     */
    public function advancedSearch(array $criteria): array
    {
        $query = Product::query()
            ->with(['manufacturer', 'category'])
            ->active()
            ->available();

        // Text-based search with scoring
        if (!empty($criteria['search_term'])) {
            $searchTerm = $criteria['search_term'];
            $query = $this->addTextSearch($query, $searchTerm);
        }

        // NDC search with normalization
        if (!empty($criteria['ndc'])) {
            $normalizedNdc = $this->normalizeNdc($criteria['ndc']);
            $query->where('ndc_number', 'like', "%{$normalizedNdc}%");
        }

        // Category filters
        if (!empty($criteria['category_ids'])) {
            $query->whereIn('category_id', $criteria['category_ids']);
        }

        // Manufacturer filters
        if (!empty($criteria['manufacturer_ids'])) {
            $query->whereIn('manufacturer_id', $criteria['manufacturer_ids']);
        }

        // DEA Schedule filter
        if (!empty($criteria['dea_schedules'])) {
            $query->whereIn('dea_schedule', $criteria['dea_schedules']);
        }

        // Prescription type filters
        if (isset($criteria['requires_prescription'])) {
            $query->where('requires_prescription', $criteria['requires_prescription']);
        }

        if (isset($criteria['is_controlled'])) {
            $query->where('is_controlled_substance', $criteria['is_controlled']);
        }

        if (isset($criteria['is_otc'])) {
            $query->where('is_otc', $criteria['is_otc']);
        }

        // Price range
        if (!empty($criteria['min_price'])) {
            $query->where('retail_price', '>=', $criteria['min_price']);
        }

        if (!empty($criteria['max_price'])) {
            $query->where('retail_price', '<=', $criteria['max_price']);
        }

        // Stock filters
        if (!empty($criteria['stock_status'])) {
            $this->applyStockFilter($query, $criteria['stock_status']);
        }

        // Dosage form filter
        if (!empty($criteria['dosage_forms'])) {
            $query->whereIn('dosage_form', $criteria['dosage_forms']);
        }

        // Strength filter (fuzzy matching)
        if (!empty($criteria['strength'])) {
            $query->where('strength', 'like', "%{$criteria['strength']}%");
        }

        // Route of administration
        if (!empty($criteria['routes'])) {
            $query->whereIn('route_of_administration', $criteria['routes']);
        }

        // Generic/Brand preference
        if (isset($criteria['is_generic'])) {
            $query->where('is_generic', $criteria['is_generic']);
        }

        // Apply sorting
        $this->applySorting($query, $criteria);

        // Get results
        $limit = $criteria['limit'] ?? 50;
        $results = $query->limit($limit)->get();

        return [
            'products' => $results,
            'total_found' => $query->count(),
            'search_criteria' => $criteria,
            'suggestions' => $this->generateSearchSuggestions($criteria, $results)
        ];
    }

    /**
     * Search by NDC with intelligent matching.
     */
    public function searchByNdc(string $ndc): ?Product
    {
        $normalizedNdc = $this->normalizeNdc($ndc);
        
        // Try exact match first
        $product = Product::where('ndc_number', $normalizedNdc)
            ->active()
            ->available()
            ->first();

        if ($product) {
            return $product;
        }

        // Try partial matches for common NDC formats
        $ndcVariations = $this->generateNdcVariations($normalizedNdc);
        
        foreach ($ndcVariations as $variation) {
            $product = Product::where('ndc_number', 'like', "%{$variation}%")
                ->active()
                ->available()
                ->first();
            
            if ($product) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Quick search for autocomplete functionality.
     */
    public function quickSearch(string $query, int $limit = 10): Collection
    {
        $cacheKey = "quick_search:" . md5($query) . ":{$limit}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $limit) {
            return Product::query()
                ->select(['id', 'name', 'brand_name', 'generic_name', 'ndc_number', 'strength', 'dosage_form'])
                ->where(function (Builder $q) use ($query) {
                    $q->where('name', 'like', "{$query}%")
                      ->orWhere('brand_name', 'like', "{$query}%")
                      ->orWhere('generic_name', 'like', "{$query}%")
                      ->orWhere('ndc_number', 'like', "{$query}%");
                })
                ->active()
                ->available()
                ->orderByRaw("
                    CASE 
                        WHEN name LIKE '{$query}%' THEN 1
                        WHEN brand_name LIKE '{$query}%' THEN 2
                        WHEN generic_name LIKE '{$query}%' THEN 3
                        WHEN ndc_number LIKE '{$query}%' THEN 4
                        ELSE 5
                    END
                ")
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Search for drug interactions between active ingredients.
     */
    public function searchInteractingDrugs(array $activeIngredients): array
    {
        $interactions = [];
        
        foreach ($activeIngredients as $ingredient) {
            $interactingProducts = Product::where('drug_interactions', 'like', "%{$ingredient}%")
                ->orWhereJsonContains('drug_interactions', $ingredient)
                ->active()
                ->available()
                ->get();

            if ($interactingProducts->isNotEmpty()) {
                $interactions[$ingredient] = $interactingProducts;
            }
        }

        return $interactions;
    }

    /**
     * Find similar medications based on active ingredients.
     */
    public function findSimilarMedications(Product $product, int $limit = 10): Collection
    {
        $activeIngredients = $product->getActiveIngredientsList();
        
        if (empty($activeIngredients)) {
            return new Collection();
        }

        $query = Product::query()
            ->where('id', '!=', $product->id)
            ->active()
            ->available();

        // Search for products with matching active ingredients
        foreach ($activeIngredients as $ingredient) {
            $query->orWhere('active_ingredients', 'like', "%{$ingredient}%")
                  ->orWhereJsonContains('active_ingredients', $ingredient);
        }

        return $query->with(['manufacturer', 'category'])
            ->limit($limit)
            ->get();
    }

    /**
     * Search for generic equivalents.
     */
    public function findGenericEquivalents(Product $product): Collection
    {
        if ($product->is_generic) {
            // If this is already generic, find the brand version
            if ($product->brandEquivalent) {
                return collect([$product->brandEquivalent]);
            }
            return new Collection();
        }

        // Find generic versions
        return $product->genericEquivalents()
            ->active()
            ->available()
            ->get();
    }

    /**
     * Search medications by therapeutic class.
     */
    public function searchByTherapeuticClass(string $therapeuticClass, int $limit = 20): Collection
    {
        return Product::query()
            ->whereHas('category', function (Builder $query) use ($therapeuticClass) {
                $query->where('therapeutic_class', 'like', "%{$therapeuticClass}%");
            })
            ->with(['manufacturer', 'category'])
            ->active()
            ->available()
            ->limit($limit)
            ->get();
    }

    /**
     * Get medication alternatives based on contraindications.
     */
    public function findAlternatives(array $contraindications, string $therapeuticClass): Collection
    {
        return Product::query()
            ->whereHas('category', function (Builder $query) use ($therapeuticClass) {
                $query->where('therapeutic_class', 'like', "%{$therapeuticClass}%");
            })
            ->where(function (Builder $query) use ($contraindications) {
                foreach ($contraindications as $contraindication) {
                    $query->where('contraindications', 'not like', "%{$contraindication}%");
                }
            })
            ->with(['manufacturer', 'category'])
            ->active()
            ->available()
            ->get();
    }

    /**
     * Advanced inventory search for pharmacy management.
     */
    public function inventorySearch(array $criteria): Collection
    {
        $query = Product::query()->with(['manufacturer', 'category']);

        // Low stock items
        if (!empty($criteria['low_stock'])) {
            $query->lowStock();
        }

        // Expiring items
        if (!empty($criteria['expiring_days'])) {
            $query->expiringSoon($criteria['expiring_days']);
        }

        // Controlled substances
        if (!empty($criteria['controlled_only'])) {
            $query->controlled();
        }

        // High-value items
        if (!empty($criteria['min_value'])) {
            $query->where('retail_price', '>=', $criteria['min_value']);
        }

        return $query->get();
    }

    /**
     * Private helper methods
     */
    private function addTextSearch(Builder $query, string $searchTerm): Builder
    {
        return $query->where(function (Builder $q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('brand_name', 'like', "%{$searchTerm}%")
              ->orWhere('generic_name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhere('active_ingredients', 'like', "%{$searchTerm}%");
        });
    }

    private function normalizeNdc(string $ndc): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $ndc);
        
        // Common NDC format is 11 digits: XXXXX-YYYY-ZZ or XXXX-YYYY-ZZ
        if (strlen($cleaned) === 11) {
            return substr($cleaned, 0, 5) . '-' . substr($cleaned, 5, 4) . '-' . substr($cleaned, 9, 2);
        } elseif (strlen($cleaned) === 10) {
            return substr($cleaned, 0, 4) . '-' . substr($cleaned, 4, 4) . '-' . substr($cleaned, 8, 2);
        }
        
        return $cleaned;
    }

    private function generateNdcVariations(string $ndc): array
    {
        $variations = [];
        $cleaned = preg_replace('/[^0-9]/', '', $ndc);
        
        // Try different formatting
        $variations[] = $cleaned;
        
        if (strlen($cleaned) >= 10) {
            $variations[] = substr($cleaned, 0, 4) . '-' . substr($cleaned, 4, 4) . '-' . substr($cleaned, 8);
            $variations[] = substr($cleaned, 0, 5) . '-' . substr($cleaned, 5, 4) . '-' . substr($cleaned, 9);
        }
        
        return array_unique($variations);
    }

    private function applyStockFilter(Builder $query, string $stockStatus): void
    {
        switch ($stockStatus) {
            case 'in_stock':
                $query->inStock();
                break;
            case 'low_stock':
                $query->lowStock();
                break;
            case 'out_of_stock':
                $query->where('quantity_on_hand', '<=', 0);
                break;
        }
    }

    private function applySorting(Builder $query, array $criteria): void
    {
        $sortBy = $criteria['sort_by'] ?? 'relevance';
        $sortDirection = $criteria['sort_direction'] ?? 'asc';

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'price':
                $query->orderBy('retail_price', $sortDirection);
                break;
            case 'manufacturer':
                $query->join('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
                      ->orderBy('manufacturers.name', $sortDirection);
                break;
            case 'stock':
                $query->orderBy('quantity_on_hand', $sortDirection);
                break;
            case 'expiration':
                $query->orderBy('expiration_date', $sortDirection);
                break;
            default:
                // Relevance sorting (by name match quality)
                if (!empty($criteria['search_term'])) {
                    $term = $criteria['search_term'];
                    $query->orderByRaw("
                        CASE 
                            WHEN name = '{$term}' THEN 1
                            WHEN brand_name = '{$term}' THEN 2
                            WHEN generic_name = '{$term}' THEN 3
                            WHEN name LIKE '{$term}%' THEN 4
                            WHEN brand_name LIKE '{$term}%' THEN 5
                            WHEN generic_name LIKE '{$term}%' THEN 6
                            ELSE 7
                        END
                    ");
                } else {
                    $query->orderBy('name', 'asc');
                }
                break;
        }
    }

    private function generateSearchSuggestions(array $criteria, Collection $results): array
    {
        $suggestions = [];

        // If no results, suggest alternatives
        if ($results->isEmpty()) {
            if (!empty($criteria['search_term'])) {
                // Find similar product names
                $similarProducts = Product::where('name', 'like', "%{$criteria['search_term']}%")
                    ->orWhere('generic_name', 'like', "%{$criteria['search_term']}%")
                    ->active()
                    ->available()
                    ->limit(5)
                    ->get(['name', 'generic_name']);

                $suggestions['similar_names'] = $similarProducts->pluck('name')->unique()->values();
            }

            $suggestions['try_removing'] = [];
            if (!empty($criteria['dea_schedules'])) {
                $suggestions['try_removing'][] = 'DEA schedule filter';
            }
            if (!empty($criteria['manufacturer_ids'])) {
                $suggestions['try_removing'][] = 'manufacturer filter';
            }
        }

        // Suggest related categories
        if (!empty($criteria['category_ids'])) {
            $relatedCategories = Category::whereIn('parent_id', $criteria['category_ids'])
                ->orWhereIn('id', function ($query) use ($criteria) {
                    $query->select('parent_id')
                          ->from('categories')
                          ->whereIn('id', $criteria['category_ids']);
                })
                ->active()
                ->get(['id', 'name']);

            $suggestions['related_categories'] = $relatedCategories;
        }

        return $suggestions;
    }
}