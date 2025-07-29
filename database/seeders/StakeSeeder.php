<?php

namespace Database\Seeders;

use App\Models\Stake;
use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Seeder;

class StakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $markets = Market::where('status', 'open')->get();
        $users = User::where('id', '>', 1)->get(); // Exclude admin user

        foreach ($markets as $market) {
            // Create 3-15 stakes per market
            $stakeCount = rand(3, 15);
            
            for ($i = 0; $i < $stakeCount; $i++) {
                $user = $users->random();
                $amount = rand(500, 50000) * 100; // 500-50,000 NGN in kobo
                $position = rand(0, 1) ? 'yes' : 'no';
                
                // Ensure user has sufficient balance
                if ($user->wallet && $user->wallet->balance >= $amount) {
                    Stake::create([
                        'user_id' => $user->id,
                        'market_id' => $market->id,
                        'amount' => $amount,
                        'side' => $position,
                        'odds_at_placement' => rand(1500, 8500) / 10000, // Convert to decimal
                        'created_at' => now()->subDays(rand(0, 30)),
                        'updated_at' => now()->subDays(rand(0, 30)),
                    ]);
                    
                    // Update user's wallet balance
                    $user->wallet->decrement('balance', $amount);
                }
            }
        }

        // Create some stakes for closed markets with resolved outcomes
        $closedMarkets = Market::where('status', 'closed')->get();
        
        foreach ($closedMarkets as $market) {
            $stakeCount = rand(5, 20);
            
            for ($i = 0; $i < $stakeCount; $i++) {
                $user = $users->random();
                $amount = rand(1000, 100000) * 100;
                $position = rand(0, 1) ? 'yes' : 'no';
                $won = rand(0, 1); // 50% chance of winning
                
                $stake = Stake::create([
                    'user_id' => $user->id,
                    'market_id' => $market->id,
                    'side' => $position,
                    'amount' => $amount,
                    'odds_at_placement' => rand(1500, 8500),
                    'created_at' => now()->subDays(rand(30, 90)),
                    'updated_at' => now()->subDays(rand(30, 90)),
                ]);
            }
        }
    }
}
