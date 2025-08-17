<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Direct Meds pharmacy platform
        $permissions = [
            // User management permissions
            'user:create',
            'user:read',
            'user:update',
            'user:delete',
            'user:list',
            'user:search',
            'user:activate',
            'user:deactivate',
            'user:impersonate',

            // Profile management permissions
            'profile:read',
            'profile:update',
            'profile:delete',
            'profile:view-sensitive', // PHI data

            // Patient-specific permissions
            'patient:read',
            'patient:update',
            'patient:list',
            'patient:search',
            'patient:medical-history',
            'patient:prescriptions',
            'patient:orders',

            // Prescription management permissions
            'prescription:create',
            'prescription:read',
            'prescription:update',
            'prescription:delete',
            'prescription:list',
            'prescription:approve',
            'prescription:reject',
            'prescription:dispense',
            'prescription:refill',
            'prescription:transfer',
            'prescription:cancel',

            // Medication and inventory permissions
            'medication:read',
            'medication:create',
            'medication:update',
            'medication:delete',
            'medication:list',
            'inventory:read',
            'inventory:update',
            'inventory:manage',
            'inventory:reports',

            // Order management permissions
            'order:create',
            'order:read',
            'order:update',
            'order:delete',
            'order:list',
            'order:process',
            'order:ship',
            'order:cancel',
            'order:refund',

            // Pharmacy management permissions
            'pharmacy:read',
            'pharmacy:update',
            'pharmacy:manage',
            'pharmacy:reports',
            'pharmacy:analytics',

            // Consultation permissions
            'consultation:create',
            'consultation:read',
            'consultation:update',
            'consultation:delete',
            'consultation:schedule',
            'consultation:conduct',

            // Billing and payment permissions
            'billing:read',
            'billing:create',
            'billing:update',
            'billing:process',
            'billing:refund',
            'payment:process',
            'payment:refund',
            'payment:reports',

            // Insurance permissions
            'insurance:verify',
            'insurance:submit',
            'insurance:track',

            // Reporting and analytics permissions
            'reports:view',
            'reports:generate',
            'reports:export',
            'analytics:view',
            'analytics:advanced',

            // Administrative permissions
            'admin:system',
            'admin:users',
            'admin:roles',
            'admin:permissions',
            'admin:settings',
            'admin:logs',
            'admin:audit',
            'admin:backup',

            // HIPAA and compliance permissions
            'hipaa:access',
            'hipaa:audit',
            'compliance:view',
            'compliance:manage',

            // API permissions
            'api:access',
            'api:admin',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Patient Role
        $patientRole = Role::create(['name' => 'patient']);
        $patientRole->givePermissionTo([
            'user:read',
            'user:update',
            'profile:read',
            'profile:update',
            'patient:read',
            'patient:update',
            'patient:medical-history',
            'patient:prescriptions',
            'patient:orders',
            'prescription:read',
            'prescription:refill',
            'medication:read',
            'order:create',
            'order:read',
            'order:cancel',
            'consultation:create',
            'consultation:read',
            'consultation:schedule',
            'billing:read',
            'insurance:verify',
            'api:access',
        ]);

        // 2. Pharmacist Role
        $pharmacistRole = Role::create(['name' => 'pharmacist']);
        $pharmacistRole->givePermissionTo([
            'user:read',
            'user:update',
            'profile:read',
            'profile:update',
            'patient:read',
            'patient:list',
            'patient:search',
            'patient:medical-history',
            'patient:prescriptions',
            'prescription:read',
            'prescription:update',
            'prescription:dispense',
            'prescription:refill',
            'prescription:transfer',
            'prescription:list',
            'medication:read',
            'medication:list',
            'inventory:read',
            'inventory:update',
            'inventory:manage',
            'order:read',
            'order:update',
            'order:process',
            'order:ship',
            'order:list',
            'pharmacy:read',
            'pharmacy:update',
            'consultation:read',
            'billing:read',
            'billing:create',
            'billing:update',
            'billing:process',
            'insurance:verify',
            'insurance:submit',
            'insurance:track',
            'reports:view',
            'reports:generate',
            'hipaa:access',
            'api:access',
        ]);

        // 3. Prescriber Role (Doctors, Nurse Practitioners)
        $prescriberRole = Role::create(['name' => 'prescriber']);
        $prescriberRole->givePermissionTo([
            'user:read',
            'user:update',
            'profile:read',
            'profile:update',
            'profile:view-sensitive',
            'patient:read',
            'patient:update',
            'patient:list',
            'patient:search',
            'patient:medical-history',
            'patient:prescriptions',
            'prescription:create',
            'prescription:read',
            'prescription:update',
            'prescription:approve',
            'prescription:cancel',
            'prescription:list',
            'medication:read',
            'medication:list',
            'consultation:create',
            'consultation:read',
            'consultation:update',
            'consultation:schedule',
            'consultation:conduct',
            'billing:read',
            'billing:create',
            'insurance:verify',
            'reports:view',
            'hipaa:access',
            'api:access',
        ]);

        // 4. Admin Role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all()); // Give all permissions to admin

        // 5. Super Admin Role (for system administrators)
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // 6. Pharmacy Manager Role
        $pharmacyManagerRole = Role::create(['name' => 'pharmacy-manager']);
        $pharmacyManagerRole->givePermissionTo([
            'user:read',
            'user:update',
            'user:list',
            'profile:read',
            'profile:update',
            'patient:read',
            'patient:list',
            'patient:search',
            'prescription:read',
            'prescription:list',
            'prescription:approve',
            'prescription:dispense',
            'medication:read',
            'medication:create',
            'medication:update',
            'medication:list',
            'inventory:read',
            'inventory:update',
            'inventory:manage',
            'inventory:reports',
            'order:read',
            'order:update',
            'order:list',
            'order:process',
            'pharmacy:read',
            'pharmacy:update',
            'pharmacy:manage',
            'pharmacy:reports',
            'pharmacy:analytics',
            'billing:read',
            'billing:create',
            'billing:update',
            'billing:process',
            'payment:process',
            'payment:reports',
            'insurance:verify',
            'insurance:submit',
            'insurance:track',
            'reports:view',
            'reports:generate',
            'reports:export',
            'analytics:view',
            'hipaa:access',
            'hipaa:audit',
            'compliance:view',
            'api:access',
        ]);

        // 7. Pharmacy Technician Role
        $pharmacyTechRole = Role::create(['name' => 'pharmacy-technician']);
        $pharmacyTechRole->givePermissionTo([
            'user:read',
            'profile:read',
            'profile:update',
            'patient:read',
            'patient:search',
            'prescription:read',
            'prescription:list',
            'medication:read',
            'medication:list',
            'inventory:read',
            'inventory:update',
            'order:read',
            'order:update',
            'order:process',
            'billing:read',
            'insurance:verify',
            'hipaa:access',
            'api:access',
        ]);

        // 8. Insurance Coordinator Role
        $insuranceCoordinatorRole = Role::create(['name' => 'insurance-coordinator']);
        $insuranceCoordinatorRole->givePermissionTo([
            'user:read',
            'profile:read',
            'patient:read',
            'patient:list',
            'patient:search',
            'prescription:read',
            'prescription:list',
            'billing:read',
            'billing:create',
            'billing:update',
            'billing:process',
            'insurance:verify',
            'insurance:submit',
            'insurance:track',
            'reports:view',
            'reports:generate',
            'hipaa:access',
            'api:access',
        ]);

        $this->command->info('Created ' . count($permissions) . ' permissions');
        $this->command->info('Created 8 roles with appropriate permissions');
        $this->command->info('Roles created: patient, pharmacist, prescriber, admin, super-admin, pharmacy-manager, pharmacy-technician, insurance-coordinator');
    }
}