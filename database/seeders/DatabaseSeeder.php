<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear cached roles/permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure roles (guard 'web')
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole  = Role::firstOrCreate(['name' => 'users', 'guard_name' => 'web']);

        // Admin user (update or insert by email)
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                // Re-hash every run (ok). If you prefer, only set when wasRecentlyCreated.
                'password' => Hash::make('admin@example.com'),
            ]
        );
        // Ensure the role is exactly 'admin'
        $admin->syncRoles([$adminRole]);

        // Test user (update or insert by email)
        $testUser = User::updateOrCreate(
            ['email' => 'bharanisrinivasan1@gmail.com'],
            [
                'name'     => 'Test User',
                'password' => Hash::make('bharanisrinivasan1@gmail.com'),
            ]
        );
        // Ensure the role is exactly 'users'
        $testUser->syncRoles([$userRole]);

        // Seed property types & amenities
        $this->call([
            PropertyTypeAndAmenitySeeder::class,
        ]);
    }
}
