<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $promoCodes = [
            [
                'code' => 'WELCOME10',
                'discount_percent' => 10.00,
                'usage_limit' => 100,
                'used_count' => rand(5, 25),
                'expires_at' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'code' => 'NEWBIE15',
                'discount_percent' => 15.00,
                'usage_limit' => 500,
                'used_count' => rand(100, 300),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'SPORTS25',
                'discount_percent' => 25.00,
                'usage_limit' => 200,
                'used_count' => rand(20, 80),
                'expires_at' => now()->addWeeks(8),
                'is_active' => true,
            ],
            [
                'code' => 'POLITICS20',
                'discount_percent' => 20.00,
                'usage_limit' => 300,
                'used_count' => rand(30, 120),
                'expires_at' => now()->addMonths(4),
                'is_active' => true,
            ],
            [
                'code' => 'BIGBET30',
                'discount_percent' => 30.00,
                'usage_limit' => 100,
                'used_count' => rand(10, 50),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'EXPIRED2024',
                'discount_percent' => 20.00,
                'usage_limit' => 50,
                'used_count' => 50, // Fully used
                'expires_at' => now()->subMonths(1), // Expired
                'is_active' => false,
            ],
        ];

        foreach ($promoCodes as $promoData) {
            PromoCode::create($promoData);
        }
    }
}
