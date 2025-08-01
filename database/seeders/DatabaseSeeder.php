<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            UserSeeder::class,
            WalletSeeder::class,
            MarketSeeder::class,
            StakeSeeder::class,
            PromoCodeSeeder::class,
            ReferralSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
