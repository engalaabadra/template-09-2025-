<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\Geocodes\CountrySeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\BoardSeeder;
use Database\Seeders\PaymentMethodSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(BannerSeeder::class);
        $this->call(BoardSeeder::class);
        $this->call(PaymentMethodSeeder::class);
    }
}
