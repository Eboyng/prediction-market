<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        $marketTemplates = [
            // Politics
            [
                'title' => 'Will Bola Tinubu win the 2027 Nigerian Presidential Election?',
                'description' => 'Predict whether Bola Ahmed Tinubu will be re-elected as President of Nigeria in the 2027 general elections.',
                'category' => 'politics',
                'closes_days' => 1095, // 3 years
            ],
            [
                'title' => 'Will Nigeria hold its general elections on schedule in 2027?',
                'description' => 'Will the 2027 Nigerian general elections take place as constitutionally scheduled without postponement?',
                'category' => 'politics',
                'closes_days' => 1000,
            ],
            
            // Sports
            [
                'title' => 'Will Nigeria qualify for the 2026 FIFA World Cup?',
                'description' => 'Will the Super Eagles of Nigeria qualify for the 2026 FIFA World Cup in USA, Canada, and Mexico?',
                'category' => 'sports',
                'closes_days' => 500,
            ],
            [
                'title' => 'Will Manchester City win the Premier League 2024/25 season?',
                'description' => 'Will Manchester City FC be crowned Premier League champions for the 2024/25 season?',
                'category' => 'sports',
                'closes_days' => 180,
            ],
            [
                'title' => 'Will Victor Osimhen score 20+ goals this season?',
                'description' => 'Will Nigerian striker Victor Osimhen score 20 or more goals in all competitions this season?',
                'category' => 'sports',
                'closes_days' => 200,
            ],
            
            // Entertainment
            [
                'title' => 'Will Burna Boy win a Grammy in 2025?',
                'description' => 'Will Nigerian artist Burna Boy win at least one Grammy Award at the 2025 ceremony?',
                'category' => 'entertainment',
                'closes_days' => 120,
            ],
            [
                'title' => 'Will Nollywood produce a $10M+ budget film in 2025?',
                'description' => 'Will any Nollywood production have a budget of $10 million or more in 2025?',
                'category' => 'entertainment',
                'closes_days' => 365,
            ],
            
            // Technology
            [
                'title' => 'Will Nigeria launch 5G nationwide by end of 2025?',
                'description' => 'Will 5G network coverage be available in all 36 Nigerian states by December 31, 2025?',
                'category' => 'technology',
                'closes_days' => 400,
            ],
            [
                'title' => 'Will Tesla stock reach $300 by end of 2025?',
                'description' => 'Will Tesla (TSLA) stock price reach or exceed $300 per share by December 31, 2025?',
                'category' => 'technology',
                'closes_days' => 365,
            ],
            
            // Economics
            [
                'title' => 'Will USD/NGN exchange rate exceed ₦2000 in 2025?',
                'description' => 'Will the US Dollar to Nigerian Naira exchange rate reach or exceed ₦2000 per dollar in 2025?',
                'category' => 'economics',
                'closes_days' => 365,
            ],
            [
                'title' => 'Will Nigeria\'s inflation rate drop below 15% in 2025?',
                'description' => 'Will Nigeria\'s annual inflation rate fall below 15% at any point during 2025?',
                'category' => 'economics',
                'closes_days' => 365,
            ],
            
            // Weather
            [
                'title' => 'Will Lagos experience flooding in the 2025 rainy season?',
                'description' => 'Will Lagos State experience significant flooding during the 2025 rainy season (April-October)?',
                'category' => 'weather',
                'closes_days' => 120,
            ],
            
            // Social Media
            [
                'title' => 'Will X (Twitter) have more than 1B active users by 2026?',
                'description' => 'Will X (formerly Twitter) reach 1 billion monthly active users by the end of 2026?',
                'category' => 'social-media',
                'closes_days' => 730,
            ],
            
            // Cryptocurrency
            [
                'title' => 'Will Bitcoin reach $150,000 by end of 2025?',
                'description' => 'Will Bitcoin (BTC) price reach or exceed $150,000 USD by December 31, 2025?',
                'category' => 'cryptocurrency',
                'closes_days' => 365,
            ],
            [
                'title' => 'Will Nigeria launch a Central Bank Digital Currency (CBDC) in 2025?',
                'description' => 'Will the Central Bank of Nigeria officially launch and make available a digital naira (eNaira 2.0) in 2025?',
                'category' => 'cryptocurrency',
                'closes_days' => 365,
            ],
        ];

        foreach ($marketTemplates as $template) {
            $category = $categories->where('slug', $template['category'])->first();
            if (!$category) continue;

            $closesAt = Carbon::now()->addDays($template['closes_days']);
            $status = rand(1, 10) > 8 ? 'closed' : 'open'; // 20% chance of being closed
            
            if ($status === 'closed') {
                $closesAt = Carbon::now()->subDays(rand(1, 30));
            }

            Market::create([
                'question' => $template['title'],
                'description' => $template['description'],
                'category_id' => $category->id,
                'status' => $status === 'closed' ? 'resolved' : 'open',
                'closes_at' => $closesAt,
                'created_at' => Carbon::now()->subDays(rand(0, 60)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);
        }

        // Create additional random markets
        $additionalMarkets = [
            'Will Nigeria qualify for the 2026 FIFA World Cup?',
            'Will Bitcoin reach $100,000 USD by end of 2025?',
            'Will Lagos State complete the Blue Line rail project in 2025?',
            'Will Dangote Refinery achieve 100% capacity by 2025?',
            'Will Nigeria achieve 24-hour electricity supply in any state by 2026?',
            'Will the Nigerian Naira strengthen against the US Dollar in 2025?',
            'Will any Nigerian tech startup achieve unicorn status in 2025?',
            'Will Nigeria host the African Cup of Nations before 2030?',
            'Will renewable energy contribute 30% of Nigeria\'s power by 2027?',
            'Will Nigeria\'s inflation rate drop below 15% in 2025?',
        ];

        foreach ($additionalMarkets as $index => $question) {
            $randomCategory = $categories->random();
            
            Market::create([
                'question' => $question,
                'description' => 'A prediction market about ' . strtolower($question),
                'category_id' => $randomCategory->id,
                'status' => 'open',
                'closes_at' => now()->addDays(rand(30, 365)),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now()->subDays(rand(0, 15)),
            ]);
        }
    }
}
