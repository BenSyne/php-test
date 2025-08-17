<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@directmeds.com',
            'password' => Hash::make('password123'),
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'is_active' => true,
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => '127.0.0.1',
        ]);

        UserProfile::create([
            'user_id' => $admin->id,
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
        ]);

        $admin->assignRole('admin');

        // Create pharmacist user
        $pharmacist = User::create([
            'name' => 'Dr. Sarah Johnson',
            'email' => 'pharmacist@directmeds.com',
            'password' => Hash::make('password123'),
            'user_type' => 'pharmacist',
            'email_verified_at' => now(),
            'is_active' => true,
            'license_number' => 'RX123456',
            'license_state' => 'CA',
            'license_expiry' => now()->addYears(2),
            'npi_number' => '1234567890',
            'phone' => '+1-555-0101',
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => '127.0.0.1',
        ]);

        UserProfile::create([
            'user_id' => $pharmacist->id,
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'specialization' => 'Clinical Pharmacy',
            'medical_school' => 'UCSF School of Pharmacy',
            'graduation_year' => 2015,
            'bio' => 'Experienced clinical pharmacist with 8+ years in retail and hospital pharmacy.',
            'address_line_1' => '123 Pharmacy St',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94102',
            'phone_mobile' => '+1-555-0101',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
        ]);

        $pharmacist->assignRole('pharmacist');

        // Create prescriber user
        $prescriber = User::create([
            'name' => 'Dr. Michael Chen',
            'email' => 'prescriber@directmeds.com',
            'password' => Hash::make('password123'),
            'user_type' => 'prescriber',
            'email_verified_at' => now(),
            'is_active' => true,
            'license_number' => 'MD789012',
            'license_state' => 'CA',
            'license_expiry' => now()->addYears(2),
            'dea_number' => 'BC1234567',
            'npi_number' => '9876543210',
            'phone' => '+1-555-0102',
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => '127.0.0.1',
        ]);

        UserProfile::create([
            'user_id' => $prescriber->id,
            'first_name' => 'Michael',
            'last_name' => 'Chen',
            'specialization' => 'Family Medicine',
            'medical_school' => 'Stanford School of Medicine',
            'graduation_year' => 2010,
            'bio' => 'Board-certified family physician providing comprehensive primary care.',
            'consultation_fee' => '$200',
            'address_line_1' => '456 Medical Center Dr',
            'city' => 'Palo Alto',
            'state' => 'CA',
            'postal_code' => '94301',
            'phone_mobile' => '+1-555-0102',
            'certifications' => [
                'Board Certified Family Medicine',
                'ACLS Certified',
                'BLS Certified'
            ],
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
        ]);

        $prescriber->assignRole('prescriber');

        // Create patient user
        $patient = User::create([
            'name' => 'Jane Smith',
            'email' => 'patient@directmeds.com',
            'password' => Hash::make('password123'),
            'user_type' => 'patient',
            'email_verified_at' => now(),
            'is_active' => true,
            'phone' => '+1-555-0103',
            'date_of_birth' => '1985-06-15',
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => '127.0.0.1',
        ]);

        UserProfile::create([
            'user_id' => $patient->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'gender' => 'female',
            'address_line_1' => '789 Patient Ave',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90210',
            'phone_mobile' => '+1-555-0103',
            'emergency_contact_name' => 'John Smith',
            'emergency_contact_phone' => '+1-555-0104',
            'emergency_contact_relationship' => 'Spouse',
            'insurance_provider' => 'Blue Cross Blue Shield',
            'insurance_policy_number' => 'BC123456789',
            'insurance_group_number' => 'GRP001',
            'insurance_expiry' => now()->addYear(),
            'allergies' => ['Penicillin', 'Shellfish'],
            'medical_conditions' => ['Hypertension', 'Type 2 Diabetes'],
            'current_medications' => ['Lisinopril 10mg', 'Metformin 500mg'],
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
            'consent_to_text' => true,
        ]);

        $patient->assignRole('patient');

        // Create pharmacy manager user
        $pharmacyManager = User::create([
            'name' => 'Lisa Rodriguez',
            'email' => 'manager@directmeds.com',
            'password' => Hash::make('password123'),
            'user_type' => 'pharmacist',
            'email_verified_at' => now(),
            'is_active' => true,
            'license_number' => 'RX654321',
            'license_state' => 'CA',
            'license_expiry' => now()->addYears(2),
            'npi_number' => '5555555555',
            'phone' => '+1-555-0105',
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => '127.0.0.1',
        ]);

        UserProfile::create([
            'user_id' => $pharmacyManager->id,
            'first_name' => 'Lisa',
            'last_name' => 'Rodriguez',
            'specialization' => 'Pharmacy Management',
            'medical_school' => 'USC School of Pharmacy',
            'graduation_year' => 2012,
            'bio' => 'Pharmacy manager with expertise in operations and compliance.',
            'address_line_1' => '321 Management Blvd',
            'city' => 'San Diego',
            'state' => 'CA',
            'postal_code' => '92101',
            'phone_mobile' => '+1-555-0105',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
        ]);

        $pharmacyManager->assignRole(['pharmacist', 'pharmacy-manager']);

        // Create pharmacy technician user
        $pharmacyTech = User::create([
            'name' => 'Robert Wilson',
            'email' => 'tech@directmeds.com',
            'password' => Hash::make('password123'),
            'user_type' => 'pharmacist',
            'email_verified_at' => now(),
            'is_active' => true,
            'license_number' => 'PT123456',
            'license_state' => 'CA',
            'license_expiry' => now()->addYears(2),
            'phone' => '+1-555-0106',
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => '127.0.0.1',
        ]);

        UserProfile::create([
            'user_id' => $pharmacyTech->id,
            'first_name' => 'Robert',
            'last_name' => 'Wilson',
            'specialization' => 'Pharmacy Technology',
            'address_line_1' => '654 Tech Street',
            'city' => 'Sacramento',
            'state' => 'CA',
            'postal_code' => '95814',
            'phone_mobile' => '+1-555-0106',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
        ]);

        $pharmacyTech->assignRole('pharmacy-technician');

        // Create insurance coordinator user
        $insuranceCoord = User::create([
            'name' => 'Maria Garcia',
            'email' => 'insurance@directmeds.com',
            'password' => Hash::make('password123'),
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'is_active' => true,
            'phone' => '+1-555-0107',
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => '127.0.0.1',
        ]);

        UserProfile::create([
            'user_id' => $insuranceCoord->id,
            'first_name' => 'Maria',
            'last_name' => 'Garcia',
            'specialization' => 'Insurance Coordination',
            'bio' => 'Experienced insurance coordinator specializing in pharmacy benefits.',
            'address_line_1' => '987 Insurance Way',
            'city' => 'Fresno',
            'state' => 'CA',
            'postal_code' => '93701',
            'phone_mobile' => '+1-555-0107',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
        ]);

        $insuranceCoord->assignRole('insurance-coordinator');

        $this->command->info('Created 7 test users:');
        $this->command->info('- Admin: admin@directmeds.com (password: password123)');
        $this->command->info('- Pharmacist: pharmacist@directmeds.com (password: password123)');
        $this->command->info('- Prescriber: prescriber@directmeds.com (password: password123)');
        $this->command->info('- Patient: patient@directmeds.com (password: password123)');
        $this->command->info('- Pharmacy Manager: manager@directmeds.com (password: password123)');
        $this->command->info('- Pharmacy Tech: tech@directmeds.com (password: password123)');
        $this->command->info('- Insurance Coordinator: insurance@directmeds.com (password: password123)');
    }
}