<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the user
        $user = User::firstOrCreate([
            'email' => 'mwendafredrick31@gmail',
        ], [
            'name' => 'mwenda',
            'password' => Hash::make('12345678'),
            'company_name' => 'Mwenda Tech Solutions',
            'is_active' => true,
            'phone' => '0712345678',
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'is_deleted' => false,
            // Change this password as needed
        ]);

        // Assign all permissions
        $permissions = Permission::all();
        $user->syncPermissions($permissions);
    }
}
