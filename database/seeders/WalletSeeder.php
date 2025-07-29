<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create wallet for each user with random balance
            $balance = rand(0, 50000000); // 0 to 500,000 NGN in kobo
            
            Wallet::create([
                'user_id' => $user->id,
                'balance' => $balance,
            ]);
        }
    }
}
