<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@predictnaira.com',
            'phone' => '+2348012345678',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_verified' => true,
            'kyc_status' => 'approved',
            'referral_code' => 'ADMIN001',
            'email_notifications' => true,
            'sms_notifications' => true,
            'in_app_notifications' => true,
            'market_updates' => true,
            'stake_confirmations' => true,
            'withdrawal_updates' => true,
            'referral_updates' => true,
            'promo_notifications' => true,
        ]);

        // Create test users
        $testUsers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+2348023456789',
                'kyc_status' => 'approved',
                'referral_code' => 'JOHN001',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '+2348034567890',
                'kyc_status' => 'pending',
                'referral_code' => 'JANE001',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike@example.com',
                'phone' => '+2348045678901',
                'kyc_status' => 'approved',
                'referral_code' => 'MIKE001',
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah@example.com',
                'phone' => '+2348056789012',
                'kyc_status' => 'rejected',
                'referral_code' => 'SARAH001',
            ],
            [
                'name' => 'David Brown',
                'email' => 'david@example.com',
                'phone' => '+2348067890123',
                'kyc_status' => 'approved',
                'referral_code' => 'DAVID001',
            ],
        ];

        foreach ($testUsers as $userData) {
            User::create(array_merge($userData, [
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_verified' => true,
                'email_notifications' => true,
                'sms_notifications' => false,
                'in_app_notifications' => true,
                'market_updates' => true,
                'stake_confirmations' => true,
                'withdrawal_updates' => true,
                'referral_updates' => false,
                'promo_notifications' => false,
            ]));
        }

        // Create additional random users
        $additionalUsers = [
            ['name' => 'John Adebayo', 'email' => 'john.adebayo@example.com', 'phone' => '+2348123456789'],
            ['name' => 'Sarah Okafor', 'email' => 'sarah.okafor@example.com', 'phone' => '+2348234567890'],
            ['name' => 'Michael Emeka', 'email' => 'michael.emeka@example.com', 'phone' => '+2348345678901'],
            ['name' => 'Grace Akinwale', 'email' => 'grace.akinwale@example.com', 'phone' => '+2348456789012'],
            ['name' => 'David Okoro', 'email' => 'david.okoro@example.com', 'phone' => '+2348567890123'],
            ['name' => 'Fatima Hassan', 'email' => 'fatima.hassan@example.com', 'phone' => '+2348678901234'],
            ['name' => 'Peter Nwosu', 'email' => 'peter.nwosu@example.com', 'phone' => '+2348789012345'],
            ['name' => 'Blessing Udo', 'email' => 'blessing.udo@example.com', 'phone' => '+2348890123456'],
            ['name' => 'Ahmed Bello', 'email' => 'ahmed.bello@example.com', 'phone' => '+2348901234567'],
            ['name' => 'Chioma Eze', 'email' => 'chioma.eze@example.com', 'phone' => '+2349012345678'],
        ];

        foreach ($additionalUsers as $userData) {
            User::create(array_merge($userData, [
                'password' => Hash::make('password'),
                'email_verified_at' => now()->subDays(rand(1, 30)),
                'is_verified' => rand(0, 1) ? true : false,
                'kyc_status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                'referral_code' => strtoupper(Str::random(8)),
                'email_notifications' => rand(0, 1) ? true : false,
                'sms_notifications' => rand(0, 1) ? true : false,
                'in_app_notifications' => true,
                'market_updates' => rand(0, 1) ? true : false,
                'stake_confirmations' => true,
                'withdrawal_updates' => true,
                'referral_updates' => rand(0, 1) ? true : false,
                'promo_notifications' => rand(0, 1) ? true : false,
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]));
        }
    }
}
