<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
            TestUserSeeder::class,
            ProductCatalogSeeder::class,
        ]);

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('You can now log in with any of the test users created.');
    }
}
