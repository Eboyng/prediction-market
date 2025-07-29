<?php

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferralSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() < 2) {
            return; // Need at least 2 users for referrals
        }

        // Create some referral relationships
        for ($i = 0; $i < 20; $i++) {
            $referrer = $users->random();
            $referee = $users->where('id', '!=', $referrer->id)->random();
            
            // Check if referral already exists
            if (Referral::where('referrer_id', $referrer->id)
                       ->where('referee_id', $referee->id)
                       ->exists()) {
                continue;
            }
            
            $bonusAmount = rand(500, 5000) * 100; // 500-5000 NGN in kobo
            $status = ['pending', 'active', 'completed'][rand(0, 2)];
            $isLocked = $status === 'pending' ? true : rand(0, 1);
            
            Referral::create([
                'referrer_id' => $referrer->id,
                'referee_id' => $referee->id,
                'bonus_amount' => $bonusAmount,
                'status' => $status,
                'is_locked' => $isLocked,
                'lock_expires_at' => $isLocked ? now()->addDays(rand(7, 30)) : null,
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);
        }
        
        // Create some pending referrals (referral codes used but not yet activated)
        for ($i = 0; $i < 10; $i++) {
            $referrer = $users->random();
            $referee = $users->where('id', '!=', $referrer->id)->random();
            
            // Check if referral already exists
            if (Referral::where('referrer_id', $referrer->id)
                       ->where('referee_id', $referee->id)
                       ->exists()) {
                continue;
            }
            
            Referral::create([
                'referrer_id' => $referrer->id,
                'referee_id' => $referee->id,
                'bonus_amount' => rand(1000, 3000) * 100, // 1000-3000 NGN in kobo
                'status' => 'pending',
                'is_locked' => true,
                'lock_expires_at' => now()->addDays(rand(1, 14)),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now()->subDays(rand(0, 7)),
            ]);
        }
    }
}
