<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding pharmaceutical manufacturers...');
        $manufacturers = $this->createManufacturers();
        
        $this->command->info('Seeding medication categories...');
        $categories = $this->createCategories();
        
        $this->command->info('Seeding pharmaceutical products...');
        $this->createProducts($manufacturers, $categories);
        
        $this->command->info('Product catalog seeding completed!');
    }

    private function createManufacturers(): array
    {
        $manufacturersData = [
            [
                'name' => 'Pfizer Inc.',
                'code' => 'PFE',
                'description' => 'Global pharmaceutical company',
                'website_url' => 'https://www.pfizer.com',
                'contact_phone' => '1-800-879-3477',
                'contact_email' => 'info@pfizer.com',
                'address' => '235 East 42nd Street',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10017',
                'country' => 'USA'
            ],
            [
                'name' => 'Johnson & Johnson',
                'code' => 'JNJ',
                'description' => 'Multinational medical devices and pharmaceutical company',
                'website_url' => 'https://www.jnj.com',
                'contact_phone' => '1-800-526-3967',
                'contact_email' => 'info@jnj.com',
                'address' => 'One Johnson & Johnson Plaza',
                'city' => 'New Brunswick',
                'state' => 'NJ',
                'zip_code' => '08933',
                'country' => 'USA'
            ],
            [
                'name' => 'Merck & Co.',
                'code' => 'MRK',
                'description' => 'Global healthcare company',
                'website_url' => 'https://www.merck.com',
                'contact_phone' => '1-800-672-6372',
                'contact_email' => 'info@merck.com',
                'address' => '2000 Galloping Hill Road',
                'city' => 'Kenilworth',
                'state' => 'NJ',
                'zip_code' => '07033',
                'country' => 'USA'
            ],
            [
                'name' => 'Novartis AG',
                'code' => 'NVS',
                'description' => 'Swiss multinational pharmaceutical company',
                'website_url' => 'https://www.novartis.com',
                'contact_phone' => '1-888-669-6682',
                'contact_email' => 'info@novartis.com',
                'address' => '1 Health Plaza',
                'city' => 'East Hanover',
                'state' => 'NJ',
                'zip_code' => '07936',
                'country' => 'USA'
            ],
            [
                'name' => 'AbbVie Inc.',
                'code' => 'ABBV',
                'description' => 'Biopharmaceutical company',
                'website_url' => 'https://www.abbvie.com',
                'contact_phone' => '1-800-255-5162',
                'contact_email' => 'info@abbvie.com',
                'address' => '1 North Waukegan Road',
                'city' => 'North Chicago',
                'state' => 'IL',
                'zip_code' => '60064',
                'country' => 'USA'
            ],
            [
                'name' => 'Teva Pharmaceuticals',
                'code' => 'TEVA',
                'description' => 'Generic pharmaceutical manufacturer',
                'website_url' => 'https://www.tevapharm.com',
                'contact_phone' => '1-888-838-2872',
                'contact_email' => 'info@teva.com',
                'address' => '1090 Horsham Road',
                'city' => 'North Wales',
                'state' => 'PA',
                'zip_code' => '19454',
                'country' => 'USA'
            ]
        ];

        $manufacturers = [];
        foreach ($manufacturersData as $data) {
            $manufacturers[] = Manufacturer::create($data);
        }

        return $manufacturers;
    }

    private function createCategories(): array
    {
        $categoriesData = [
            // Root categories
            [
                'name' => 'Cardiovascular System',
                'slug' => 'cardiovascular',
                'description' => 'Medications for heart and circulatory system',
                'therapeutic_class' => 'Cardiovascular',
                'atc_code' => 'C',
                'parent_id' => null,
                'sort_order' => 1,
                'requires_prescription' => true
            ],
            [
                'name' => 'Central Nervous System',
                'slug' => 'central-nervous-system',
                'description' => 'Medications affecting the brain and nervous system',
                'therapeutic_class' => 'CNS',
                'atc_code' => 'N',
                'parent_id' => null,
                'sort_order' => 2,
                'requires_prescription' => true
            ],
            [
                'name' => 'Anti-infectives',
                'slug' => 'anti-infectives',
                'description' => 'Antibiotics and antimicrobial agents',
                'therapeutic_class' => 'Anti-infective',
                'atc_code' => 'J',
                'parent_id' => null,
                'sort_order' => 3,
                'requires_prescription' => true
            ],
            [
                'name' => 'Pain Management',
                'slug' => 'pain-management',
                'description' => 'Analgesics and pain relief medications',
                'therapeutic_class' => 'Analgesic',
                'atc_code' => 'N02',
                'parent_id' => null,
                'sort_order' => 4,
                'requires_prescription' => true
            ],
            [
                'name' => 'Respiratory System',
                'slug' => 'respiratory',
                'description' => 'Medications for breathing and lung conditions',
                'therapeutic_class' => 'Respiratory',
                'atc_code' => 'R',
                'parent_id' => null,
                'sort_order' => 5,
                'requires_prescription' => false
            ],
            [
                'name' => 'Gastrointestinal',
                'slug' => 'gastrointestinal',
                'description' => 'Digestive system medications',
                'therapeutic_class' => 'GI',
                'atc_code' => 'A',
                'parent_id' => null,
                'sort_order' => 6,
                'requires_prescription' => false
            ]
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categories[] = Category::create($data);
        }

        // Create subcategories
        $subcategoriesData = [
            [
                'name' => 'ACE Inhibitors',
                'slug' => 'ace-inhibitors',
                'description' => 'Angiotensin-converting enzyme inhibitors',
                'therapeutic_class' => 'Cardiovascular',
                'atc_code' => 'C09A',
                'parent_id' => $categories[0]->id,
                'sort_order' => 1,
                'requires_prescription' => true
            ],
            [
                'name' => 'Beta Blockers',
                'slug' => 'beta-blockers',
                'description' => 'Beta-adrenergic blocking agents',
                'therapeutic_class' => 'Cardiovascular',
                'atc_code' => 'C07',
                'parent_id' => $categories[0]->id,
                'sort_order' => 2,
                'requires_prescription' => true
            ],
            [
                'name' => 'Antidepressants',
                'slug' => 'antidepressants',
                'description' => 'Medications for depression and anxiety',
                'therapeutic_class' => 'CNS',
                'atc_code' => 'N06A',
                'parent_id' => $categories[1]->id,
                'sort_order' => 1,
                'requires_prescription' => true
            ],
            [
                'name' => 'Antibiotics',
                'slug' => 'antibiotics',
                'description' => 'Bacterial infection treatments',
                'therapeutic_class' => 'Anti-infective',
                'atc_code' => 'J01',
                'parent_id' => $categories[2]->id,
                'sort_order' => 1,
                'requires_prescription' => true
            ]
        ];

        foreach ($subcategoriesData as $data) {
            $categories[] = Category::create($data);
        }

        return $categories;
    }

    private function createProducts(array $manufacturers, array $categories): void
    {
        $productsData = [
            // Cardiovascular medications
            [
                'name' => 'Lisinopril 10mg Tablets',
                'brand_name' => 'Prinivil',
                'generic_name' => 'Lisinopril',
                'ndc_number' => '0071-0222-23',
                'dea_schedule' => null,
                'is_controlled_substance' => false,
                'requires_prescription' => true,
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'route_of_administration' => 'Oral',
                'package_size' => 30,
                'package_type' => 'Bottle',
                'manufacturer_id' => $manufacturers[0]->id, // Pfizer
                'category_id' => $categories[6]->id, // ACE Inhibitors
                'retail_price' => 25.99,
                'cost_price' => 12.50,
                'quantity_on_hand' => 500,
                'active_ingredients' => ['Lisinopril'],
                'warnings' => 'May cause dizziness, dry cough. Do not use during pregnancy.',
                'side_effects' => 'Dizziness, headache, fatigue, dry cough',
                'contraindications' => 'Pregnancy, angioedema history',
                'drug_interactions' => ['Lithium', 'Potassium supplements', 'NSAIDs'],
                'dosage_instructions' => 'Take once daily, with or without food',
                'is_generic' => false,
                'therapeutic_equivalence_code' => 'AB'
            ],
            [
                'name' => 'Metoprolol Succinate 50mg ER Tablets',
                'brand_name' => 'Toprol-XL',
                'generic_name' => 'Metoprolol Succinate',
                'ndc_number' => '0186-0322-01',
                'dea_schedule' => null,
                'is_controlled_substance' => false,
                'requires_prescription' => true,
                'dosage_form' => 'Extended Release Tablet',
                'strength' => '50mg',
                'route_of_administration' => 'Oral',
                'package_size' => 30,
                'package_type' => 'Bottle',
                'manufacturer_id' => $manufacturers[1]->id,
                'category_id' => $categories[7]->id, // Beta Blockers
                'retail_price' => 35.50,
                'cost_price' => 18.75,
                'quantity_on_hand' => 350,
                'active_ingredients' => ['Metoprolol Succinate'],
                'warnings' => 'Do not stop abruptly. May mask signs of hypoglycemia.',
                'side_effects' => 'Fatigue, dizziness, depression, cold extremities',
                'contraindications' => 'Severe bradycardia, heart block, cardiogenic shock',
                'drug_interactions' => ['Verapamil', 'Diltiazem', 'Insulin'],
                'dosage_instructions' => 'Take once daily in the morning',
                'is_generic' => false,
                'therapeutic_equivalence_code' => 'AB'
            ],
            // Pain medications
            [
                'name' => 'Oxycodone 5mg Tablets',
                'brand_name' => 'OxyContin',
                'generic_name' => 'Oxycodone HCl',
                'ndc_number' => '59011-442-20',
                'dea_schedule' => 'CII',
                'is_controlled_substance' => true,
                'requires_prescription' => true,
                'dosage_form' => 'Tablet',
                'strength' => '5mg',
                'route_of_administration' => 'Oral',
                'package_size' => 30,
                'package_type' => 'Bottle',
                'manufacturer_id' => $manufacturers[2]->id,
                'category_id' => $categories[3]->id, // Pain Management
                'retail_price' => 85.99,
                'cost_price' => 45.50,
                'quantity_on_hand' => 100,
                'active_ingredients' => ['Oxycodone Hydrochloride'],
                'warnings' => 'HIGH RISK OF ADDICTION AND OVERDOSE. Respiratory depression risk.',
                'side_effects' => 'Drowsiness, constipation, nausea, respiratory depression',
                'contraindications' => 'Respiratory depression, acute/severe asthma, paralytic ileus',
                'drug_interactions' => ['Alcohol', 'Benzodiazepines', 'MAO inhibitors'],
                'dosage_instructions' => 'Take as directed by physician. Do not exceed prescribed dose.',
                'is_generic' => false,
                'therapeutic_equivalence_code' => 'AB'
            ],
            [
                'name' => 'Ibuprofen 200mg Tablets',
                'brand_name' => 'Advil',
                'generic_name' => 'Ibuprofen',
                'ndc_number' => '0573-0142-40',
                'dea_schedule' => null,
                'is_controlled_substance' => false,
                'requires_prescription' => false,
                'is_otc' => true,
                'dosage_form' => 'Tablet',
                'strength' => '200mg',
                'route_of_administration' => 'Oral',
                'package_size' => 100,
                'package_type' => 'Bottle',
                'manufacturer_id' => $manufacturers[1]->id,
                'category_id' => $categories[3]->id,
                'retail_price' => 12.99,
                'cost_price' => 6.50,
                'quantity_on_hand' => 800,
                'active_ingredients' => ['Ibuprofen'],
                'warnings' => 'May increase risk of heart attack or stroke. GI bleeding risk.',
                'side_effects' => 'Stomach upset, heartburn, dizziness',
                'contraindications' => 'Known allergy to NSAIDs, active GI bleeding',
                'drug_interactions' => ['Warfarin', 'ACE inhibitors', 'Lithium'],
                'dosage_instructions' => 'Take with food. Do not exceed 6 tablets in 24 hours.',
                'is_generic' => false,
                'therapeutic_equivalence_code' => 'AB'
            ],
            // Antibiotics
            [
                'name' => 'Amoxicillin 500mg Capsules',
                'brand_name' => 'Amoxil',
                'generic_name' => 'Amoxicillin',
                'ndc_number' => '0029-6008-30',
                'dea_schedule' => null,
                'is_controlled_substance' => false,
                'requires_prescription' => true,
                'dosage_form' => 'Capsule',
                'strength' => '500mg',
                'route_of_administration' => 'Oral',
                'package_size' => 30,
                'package_type' => 'Bottle',
                'manufacturer_id' => $manufacturers[0]->id,
                'category_id' => $categories[9]->id, // Antibiotics
                'retail_price' => 18.75,
                'cost_price' => 9.25,
                'quantity_on_hand' => 400,
                'active_ingredients' => ['Amoxicillin'],
                'warnings' => 'Complete full course even if feeling better. Allergy risk.',
                'side_effects' => 'Diarrhea, nausea, skin rash',
                'contraindications' => 'Penicillin allergy, mononucleosis',
                'drug_interactions' => ['Warfarin', 'Methotrexate'],
                'dosage_instructions' => 'Take every 8 hours with or without food',
                'is_generic' => false,
                'therapeutic_equivalence_code' => 'AB'
            ],
            // Antidepressants
            [
                'name' => 'Sertraline 50mg Tablets',
                'brand_name' => 'Zoloft',
                'generic_name' => 'Sertraline HCl',
                'ndc_number' => '0049-4960-66',
                'dea_schedule' => null,
                'is_controlled_substance' => false,
                'requires_prescription' => true,
                'dosage_form' => 'Tablet',
                'strength' => '50mg',
                'route_of_administration' => 'Oral',
                'package_size' => 30,
                'package_type' => 'Bottle',
                'manufacturer_id' => $manufacturers[0]->id,
                'category_id' => $categories[8]->id, // Antidepressants
                'retail_price' => 45.99,
                'cost_price' => 22.50,
                'quantity_on_hand' => 250,
                'active_ingredients' => ['Sertraline Hydrochloride'],
                'warnings' => 'Suicide risk in young adults. Withdrawal symptoms if stopped abruptly.',
                'side_effects' => 'Nausea, diarrhea, insomnia, sexual dysfunction',
                'contraindications' => 'MAO inhibitor use, pimozide use',
                'drug_interactions' => ['MAO inhibitors', 'Warfarin', 'NSAIDs'],
                'dosage_instructions' => 'Take once daily in the morning with food',
                'is_generic' => false,
                'therapeutic_equivalence_code' => 'AB'
            ],
            // Generic equivalents
            [
                'name' => 'Lisinopril 10mg Tablets (Generic)',
                'brand_name' => null,
                'generic_name' => 'Lisinopril',
                'ndc_number' => '00603-3841-21',
                'dea_schedule' => null,
                'is_controlled_substance' => false,
                'requires_prescription' => true,
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'route_of_administration' => 'Oral',
                'package_size' => 30,
                'package_type' => 'Bottle',
                'manufacturer_id' => $manufacturers[5]->id, // Teva
                'category_id' => $categories[6]->id,
                'retail_price' => 12.99,
                'cost_price' => 5.25,
                'quantity_on_hand' => 750,
                'active_ingredients' => ['Lisinopril'],
                'warnings' => 'May cause dizziness, dry cough. Do not use during pregnancy.',
                'side_effects' => 'Dizziness, headache, fatigue, dry cough',
                'contraindications' => 'Pregnancy, angioedema history',
                'drug_interactions' => ['Lithium', 'Potassium supplements', 'NSAIDs'],
                'dosage_instructions' => 'Take once daily, with or without food',
                'is_generic' => true,
                'therapeutic_equivalence_code' => 'AB',
                'brand_equivalent_id' => null // Will be set after first product is created
            ],
            [
                'name' => 'Omeprazole 20mg Capsules',
                'brand_name' => 'Prilosec',
                'generic_name' => 'Omeprazole',
                'ndc_number' => '0186-0280-31',
                'dea_schedule' => null,
                'is_controlled_substance' => false,
                'requires_prescription' => false,
                'is_otc' => true,
                'dosage_form' => 'Delayed Release Capsule',
                'strength' => '20mg',
                'route_of_administration' => 'Oral',
                'package_size' => 14,
                'package_type' => 'Blister Pack',
                'manufacturer_id' => $manufacturers[3]->id,
                'category_id' => $categories[5]->id, // GI
                'retail_price' => 19.99,
                'cost_price' => 8.75,
                'quantity_on_hand' => 300,
                'active_ingredients' => ['Omeprazole'],
                'warnings' => 'Long-term use may increase risk of bone fractures.',
                'side_effects' => 'Headache, abdominal pain, diarrhea',
                'contraindications' => 'Known hypersensitivity to proton pump inhibitors',
                'drug_interactions' => ['Warfarin', 'Clopidogrel', 'Digoxin'],
                'dosage_instructions' => 'Take once daily before eating',
                'is_generic' => false,
                'therapeutic_equivalence_code' => 'AB'
            ]
        ];

        foreach ($productsData as $data) {
            $data['slug'] = Str::slug($data['name']);
            $data['description'] = $data['warnings'] ?? 'Pharmaceutical medication';
            $data['expiration_date'] = now()->addMonths(rand(12, 36));
            $data['minimum_stock_level'] = rand(10, 50);
            $data['maximum_stock_level'] = rand(500, 1000);
            $data['storage_requirements'] = 'Store at room temperature';
            $data['storage_temperature_min'] = 15.0;
            $data['storage_temperature_max'] = 30.0;
            
            // Convert arrays to JSON for database storage
            if (isset($data['active_ingredients']) && is_array($data['active_ingredients'])) {
                $data['active_ingredients'] = json_encode($data['active_ingredients']);
            }
            if (isset($data['drug_interactions']) && is_array($data['drug_interactions'])) {
                $data['drug_interactions'] = json_encode($data['drug_interactions']);
            }

            Product::create($data);
        }

        // Update brand equivalent relationship for generic
        $brandLisinopril = Product::where('brand_name', 'Prinivil')->first();
        $genericLisinopril = Product::where('name', 'like', '%Generic%')->where('generic_name', 'Lisinopril')->first();
        
        if ($brandLisinopril && $genericLisinopril) {
            $genericLisinopril->update(['brand_equivalent_id' => $brandLisinopril->id]);
        }
    }
}
