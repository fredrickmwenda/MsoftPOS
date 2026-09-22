<?php

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AdminPermissionSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PolicySeeder;
use Database\Seeders\UserRoleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // CountriesTableSeeder::class,
            //UserSeeder::class,
            //UserRoleSeeder::class,
          // PermissionSeeder::class,
          PolicySeeder::class,
        ]);
    }
}
