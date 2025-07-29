<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Market;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $markets = Market::all();
        
        $activities = [
            'registration',
            'login',
            'logout',
            'profile_updated',
            'password_changed',
            'stake_placed',
            'stake_won',
            'stake_lost',
            'wallet_deposit',
            'wallet_withdrawal',
            'promo_code_used',
            'referral_completed',
            'kyc_submitted',
            'kyc_approved',
            'market_created',
            'notification_preferences_updated',
            'security_settings_updated',
        ];

        // Create activity logs for each user
        foreach ($users as $user) {
            $logCount = rand(5, 25);
            
            for ($i = 0; $i < $logCount; $i++) {
                $activity = $activities[array_rand($activities)];
                $metadata = [];
                
                // Add relevant metadata based on activity type
                switch ($activity) {
                    case 'stake_placed':
                        $market = $markets->random();
                        $metadata = [
                            'market_id' => $market->id,
                            'market_title' => $market->title,
                            'amount' => rand(1000, 50000) * 100,
                            'position' => rand(0, 1) ? 'yes' : 'no',
                        ];
                        break;
                        
                    case 'wallet_deposit':
                        $metadata = [
                            'amount' => rand(5000, 100000) * 100,
                            'payment_method' => rand(0, 1) ? 'paystack' : 'flutterwave',
                            'reference' => 'TXN_' . strtoupper(uniqid()),
                        ];
                        break;
                        
                    case 'wallet_withdrawal':
                        $metadata = [
                            'amount' => rand(2000, 50000) * 100,
                            'bank_name' => 'First Bank of Nigeria',
                            'account_number' => '012345****',
                        ];
                        break;
                        
                    case 'promo_redeemed':
                        $metadata = [
                            'promo_code' => 'WELCOME2025',
                            'discount_amount' => rand(500, 5000) * 100,
                        ];
                        break;
                        
                    case 'login':
                        $metadata = [
                            'ip_address' => '192.168.1.' . rand(1, 255),
                            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        ];
                        break;
                        
                    case 'profile_updated':
                        $metadata = [
                            'fields_updated' => ['name', 'phone'],
                        ];
                        break;
                }

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => $activity,
                    'metadata' => json_encode($metadata),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now()->subDays(rand(0, 90)),
                ]);
            }
        }
    }

    private function getActivityDescription(string $activity): string
    {
        $descriptions = [
            'registration' => 'User registered on the platform',
            'login' => 'User logged into the platform',
            'logout' => 'User logged out of the platform',
            'profile_updated' => 'User updated their profile information',
            'password_changed' => 'User changed their password',
            'stake_placed' => 'User placed a stake on a market',
            'stake_won' => 'User won a stake',
            'stake_lost' => 'User lost a stake',
            'wallet_deposit' => 'User deposited funds to wallet',
            'wallet_withdrawal' => 'User withdrew funds from wallet',
            'promo_code_used' => 'User applied a promo code',
            'referral_completed' => 'User completed a referral',
            'kyc_submitted' => 'User submitted KYC documents',
            'kyc_approved' => 'User KYC was approved',
            'market_created' => 'User created a new market',
            'notification_preferences_updated' => 'User updated notification preferences',
            'security_settings_updated' => 'User updated security settings',
        ];

        return $descriptions[$activity] ?? 'User performed an activity';
    }
}
