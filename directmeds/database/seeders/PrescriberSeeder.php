<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prescriber;
use App\Models\User;

class PrescriberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample prescribers for testing
        $prescribers = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'title' => 'Dr.',
                'npi_number' => '1234567890', // Valid NPI format
                'dea_number' => 'AS1234563', // Valid DEA format
                'state_license_number' => 'MD12345',
                'state_license_state' => 'MD',
                'state_license_expiry' => now()->addYear(),
                'specialty' => 'Family Medicine',
                'email' => 'dr.smith@example.com',
                'phone' => '(555) 123-4567',
                'practice_name' => 'Smith Family Practice',
                'practice_address' => '123 Medical Dr',
                'practice_city' => 'Baltimore',
                'practice_state' => 'MD',
                'practice_zip' => '21201',
                'practice_phone' => '(555) 123-4567',
                'dea_schedule' => 'II',
                'dea_expiry' => now()->addYears(3),
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_active' => true,
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'title' => 'Dr.',
                'npi_number' => '9876543210',
                'dea_number' => 'BJ9876543', // Valid DEA format
                'state_license_number' => 'VA54321',
                'state_license_state' => 'VA',
                'state_license_expiry' => now()->addMonths(18),
                'specialty' => 'Internal Medicine',
                'email' => 'dr.johnson@example.com',
                'phone' => '(555) 987-6543',
                'practice_name' => 'Johnson Internal Medicine',
                'practice_address' => '456 Health Blvd',
                'practice_city' => 'Richmond',
                'practice_state' => 'VA',
                'practice_zip' => '23219',
                'practice_phone' => '(555) 987-6543',
                'dea_schedule' => 'III',
                'dea_expiry' => now()->addYears(2),
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_active' => true,
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Davis',
                'title' => 'Dr.',
                'npi_number' => '5555555555',
                'dea_number' => 'MD5555555',
                'state_license_number' => 'CA99999',
                'state_license_state' => 'CA',
                'state_license_expiry' => now()->addMonths(6),
                'specialty' => 'Cardiology',
                'email' => 'dr.davis@example.com',
                'phone' => '(555) 555-5555',
                'practice_name' => 'Davis Cardiology Center',
                'practice_address' => '789 Heart Ave',
                'practice_city' => 'Los Angeles',
                'practice_state' => 'CA',
                'practice_zip' => '90210',
                'practice_phone' => '(555) 555-5555',
                'dea_schedule' => 'IV',
                'dea_expiry' => now()->addYears(3),
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_active' => true,
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Brown',
                'title' => 'NP',
                'npi_number' => '1111111111',
                'state_license_number' => 'TX11111',
                'state_license_state' => 'TX',
                'state_license_expiry' => now()->addYear(),
                'specialty' => 'Nurse Practitioner',
                'email' => 'np.brown@example.com',
                'phone' => '(555) 111-1111',
                'practice_name' => 'Brown Family Clinic',
                'practice_address' => '321 Care St',
                'practice_city' => 'Austin',
                'practice_state' => 'TX',
                'practice_zip' => '73301',
                'practice_phone' => '(555) 111-1111',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_active' => true,
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Wilson',
                'title' => 'Dr.',
                'npi_number' => '2222222222',
                'state_license_number' => 'FL22222',
                'state_license_state' => 'FL',
                'state_license_expiry' => now()->subDays(30), // Expired license
                'specialty' => 'Psychiatry',
                'email' => 'dr.wilson@example.com',
                'phone' => '(555) 222-2222',
                'practice_name' => 'Wilson Psychiatric Services',
                'practice_address' => '654 Mind Way',
                'practice_city' => 'Miami',
                'practice_state' => 'FL',
                'practice_zip' => '33101',
                'practice_phone' => '(555) 222-2222',
                'verification_status' => 'expired',
                'is_active' => false,
            ],
        ];

        foreach ($prescribers as $prescriberData) {
            // Find or create the verifying user (first admin user)
            $verifyingUser = User::where('user_type', 'admin')->first() ?? 
                            User::where('user_type', 'pharmacist')->first();
            
            if ($verifyingUser && $prescriberData['verification_status'] === 'verified') {
                $prescriberData['verified_by'] = $verifyingUser->id;
                $prescriberData['created_by'] = $verifyingUser->id;
            }

            Prescriber::create($prescriberData);
        }

        $this->command->info('Created ' . count($prescribers) . ' sample prescribers');
    }
}