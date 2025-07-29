<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WithdrawalFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'wallet_balance' => 5000000, // 50,000 NGN
            'kyc_status' => 'verified'
        ]);
    }

    /** @test */
    public function verified_user_can_withdraw_funds()
    {
        // Arrange
        $this->actingAs($this->user);
        Http::fake([
            'api.paystack.co/bank/resolve' => Http::response([
                'status' => true,
                'data' => ['account_name' => 'JOHN DOE']
            ], 200),
            'api.paystack.co/transferrecipient' => Http::response([
                'status' => true,
                'data' => ['recipient_code' => 'RCP_test123']
            ], 200),
            'api.paystack.co/transfer' => Http::response([
                'status' => true,
                'data' => ['transfer_code' => 'TRF_test123']
            ], 200)
        ]);

        // Act
        $response = $this->post('/withdraw', [
            'amount' => 1000000, // 10,000 NGN
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 1000000,
            'status' => 'pending'
        ]);
        
        $this->user->refresh();
        $this->assertEquals(4000000, $this->user->wallet_balance); // 50,000 - 10,000 NGN
    }

    /** @test */
    public function unverified_user_cannot_withdraw_large_amounts()
    {
        // Arrange
        $this->user->update(['kyc_status' => 'pending']);
        $this->actingAs($this->user);

        // Act
        $response = $this->post('/withdraw', [
            'amount' => 2000000, // 20,000 NGN (above KYC threshold)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseMissing('wallets', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal'
        ]);
    }

    /** @test */
    public function withdrawal_enforces_daily_limits()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Create existing withdrawal today
        Wallet::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 4000000, // 40,000 NGN
            'status' => 'completed',
            'created_at' => now()
        ]);

        // Act
        $response = $this->post('/withdraw', [
            'amount' => 2000000, // 20,000 NGN (would exceed 50,000 NGN daily limit)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Assert
        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('wallets', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 2000000
        ]);
    }

    /** @test */
    public function withdrawal_validates_minimum_amount()
    {
        // Arrange
        $this->actingAs($this->user);

        // Act
        $response = $this->post('/withdraw', [
            'amount' => 50000, // 500 NGN (below minimum)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Assert
        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function withdrawal_validates_sufficient_balance()
    {
        // Arrange
        $this->user->update(['wallet_balance' => 500000]); // 5,000 NGN
        $this->actingAs($this->user);

        // Act
        $response = $this->post('/withdraw', [
            'amount' => 1000000, // 10,000 NGN (more than balance)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Assert
        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function withdrawal_validates_bank_account()
    {
        // Arrange
        $this->actingAs($this->user);
        Http::fake([
            'api.paystack.co/bank/resolve' => Http::response([
                'status' => false,
                'message' => 'Invalid account number'
            ], 400)
        ]);

        // Act
        $response = $this->post('/withdraw', [
            'amount' => 1000000,
            'bank_code' => '058',
            'account_number' => 'invalid123',
            'account_name' => 'John Doe'
        ]);

        // Assert
        $response->assertSessionHasErrors('account_number');
    }

    /** @test */
    public function user_can_view_withdrawal_history()
    {
        // Arrange
        $this->actingAs($this->user);
        $withdrawals = Wallet::factory(3)->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'status' => 'completed'
        ]);

        // Act
        $response = $this->get('/withdrawals');

        // Assert
        $response->assertStatus(200);
        foreach ($withdrawals as $withdrawal) {
            $response->assertSee(number_format($withdrawal->amount / 100, 2));
        }
    }

    /** @test */
    public function admin_can_approve_pending_withdrawal()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $withdrawal = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 1000000,
            'status' => 'pending'
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/withdrawals/{$withdrawal->id}/approve");

        // Assert
        $response->assertRedirect();
        $withdrawal->refresh();
        $this->assertEquals('approved', $withdrawal->status);
    }

    /** @test */
    public function admin_can_reject_withdrawal()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $withdrawal = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 1000000,
            'status' => 'pending'
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/withdrawals/{$withdrawal->id}/reject", [
            'reason' => 'Suspicious activity detected'
        ]);

        // Assert
        $response->assertRedirect();
        $withdrawal->refresh();
        $this->assertEquals('rejected', $withdrawal->status);
        
        // Balance should be refunded
        $this->user->refresh();
        $this->assertEquals(6000000, $this->user->wallet_balance); // Original + refund
    }

    /** @test */
    public function withdrawal_creates_activity_log()
    {
        // Arrange
        $this->actingAs($this->user);
        Http::fake([
            'api.paystack.co/bank/resolve' => Http::response([
                'status' => true,
                'data' => ['account_name' => 'JOHN DOE']
            ], 200)
        ]);

        // Act
        $response = $this->post('/withdraw', [
            'amount' => 1000000,
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Assert
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'withdrawal_initiated',
            'description' => 'Withdrawal of ₦10,000.00 initiated'
        ]);
    }

    /** @test */
    public function guest_cannot_access_withdrawal_routes()
    {
        // Act & Assert
        $this->get('/withdraw')->assertRedirect('/login');
        $this->post('/withdraw')->assertRedirect('/login');
        $this->get('/withdrawals')->assertRedirect('/login');
    }
}
