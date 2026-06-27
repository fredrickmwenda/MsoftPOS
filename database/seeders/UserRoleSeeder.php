<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Roles;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Find or create the admin role (ID=1 or name='Admin')
        $adminRole = Roles::firstOrCreate(
            ['id' => 1],
            ['name' => 'Admin', 'is_active' => true]
        );
        $this->command->info("Admin role ID: {$adminRole->id} - Name: {$adminRole->name}");

        // 2. Find an existing admin user, or create one
        $adminUser = User::find(1) ?? User::first(); // try ID 1, then first user

        if (!$adminUser) {
            $this->command->warn("No admin user found. Creating one...");
            $adminUser = User::create([
                'name'       => 'Admin',
                'email'      => 'admin@example.com',
                'password'   => Hash::make('password'),
                'is_active'  => true,
                'is_deleted' => false,
            ]);
        }
        $this->command->info("Admin user ID: {$adminUser->id} - Email: {$adminUser->email}");

        // 3. Attach the admin role to the user
        try {
            $adminUser->roles()->sync([$adminRole->id]);
            $this->command->info("Role assigned successfully. Pivot table should now contain a record.");
        } catch (\Exception $e) {
            $this->command->error("Failed to assign role: " . $e->getMessage());
            return;
        }

        // 4. Verify
        $count = \DB::table('roles_user')->count();
        $this->command->info("Total records in roles_user: $count");
    }
}