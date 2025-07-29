<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Referral;
use App\Models\Stake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ReferralFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $referrer;
    protected User $referred;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->referrer = User::factory()->create([
            'wallet_balance' => 1000000, // 10,000 NGN
            'kyc_status' => 'verified'
        ]);
        $this->referrer->generateReferralCode();
        
        $this->referred = User::factory()->create([
            'wallet_balance' => 500000, // 5,000 NGN
            'kyc_status' => 'verified'
        ]);
    }

    /** @test */
    public function user_can_refer_another_user()
    {
        // Arrange
        $this->actingAs($this->referrer);

        // Act
        $response = $this->post('/referrals/invite', [
            'email' => 'newuser@example.com',
            'message' => 'Join this amazing prediction market!'
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('referral_invites', [
            'referrer_id' => $this->referrer->id,
            'email' => 'newuser@example.com',
            'status' => 'sent'
        ]);
    }

    /** @test */
    public function referral_is_created_when_user_registers_with_code()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'referral_code' => $this->referrer->referral_code,
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $newUser = User::where('email', $userData['email'])->first();
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $this->referrer->id,
            'referred_id' => $newUser->id,
            'status' => 'pending',
            'referral_code' => $this->referrer->referral_code
        ]);
    }

    /** @test */
    public function referral_becomes_active_when_referred_user_places_first_stake()
    {
        // Arrange
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
            'status' => 'pending'
        ]);

        $market = \App\Models\Market::factory()->create(['status' => 'open']);
        $this->actingAs($this->referred);

        // Act
        $response = $this->post("/markets/{$market->id}/stake", [
            'amount' => 100000, // 1,000 NGN
            'position' => 'yes'
        ]);

        // Assert
        $referral->refresh();
        $this->assertEquals('active', $referral->status);
        $this->assertNotNull($referral->activated_at);
    }

    /** @test */
    public function referrer_receives_bonus_when_referral_activates()
    {
        // Arrange
        config(['app.referral_bonus' => 50000]); // 500 NGN bonus
        
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
            'status' => 'pending'
        ]);

        $market = \App\Models\Market::factory()->create(['status' => 'open']);
        $this->actingAs($this->referred);

        // Act
        $response = $this->post("/markets/{$market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert
        $this->referrer->refresh();
        $this->assertEquals(1050000, $this->referrer->wallet_balance); // Original + bonus
        
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->referrer->id,
            'type' => 'referral_bonus',
            'amount' => 50000,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function referred_user_receives_welcome_bonus()
    {
        // Arrange
        config(['app.referred_user_bonus' => 25000]); // 250 NGN bonus
        
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
            'status' => 'pending'
        ]);

        $market = \App\Models\Market::factory()->create(['status' => 'open']);
        $this->actingAs($this->referred);

        // Act
        $response = $this->post("/markets/{$market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert
        $this->referred->refresh();
        $this->assertEquals(425000, $this->referred->wallet_balance); // 500k - 100k stake + 25k bonus
        
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->referred->id,
            'type' => 'referral_welcome_bonus',
            'amount' => 25000,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function user_can_view_referral_statistics()
    {
        // Arrange
        $this->actingAs($this->referrer);
        
        // Create multiple referrals
        $activeReferrals = Referral::factory(3)->create([
            'referrer_id' => $this->referrer->id,
            'status' => 'active'
        ]);
        
        $pendingReferrals = Referral::factory(2)->create([
            'referrer_id' => $this->referrer->id,
            'status' => 'pending'
        ]);

        // Act
        $response = $this->get('/referrals');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('3'); // Active referrals count
        $response->assertSee('2'); // Pending referrals count
        $response->assertSee($this->referrer->referral_code);
    }

    /** @test */
    public function user_can_view_referral_earnings()
    {
        // Arrange
        $this->actingAs($this->referrer);
        
        // Create referral earnings
        \App\Models\Wallet::factory(2)->create([
            'user_id' => $this->referrer->id,
            'type' => 'referral_bonus',
            'amount' => 50000,
            'status' => 'completed'
        ]);

        // Act
        $response = $this->get('/referrals/earnings');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('1,000.00'); // Total earnings (2 * 500 NGN)
    }

    /** @test */
    public function referral_code_is_case_insensitive()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'referral_code' => strtolower($this->referrer->referral_code), // Lowercase
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $newUser = User::where('email', $userData['email'])->first();
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $this->referrer->id,
            'referred_id' => $newUser->id
        ]);
    }

    /** @test */
    public function user_cannot_refer_themselves()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'referral_code' => $this->referrer->referral_code,
        ];

        $this->actingAs($this->referrer);

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors('referral_code');
    }

    /** @test */
    public function referral_has_expiration_limit()
    {
        // Arrange
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
            'status' => 'pending',
            'created_at' => now()->subDays(31) // 31 days old
        ]);

        $market = \App\Models\Market::factory()->create(['status' => 'open']);
        $this->actingAs($this->referred);

        // Act
        $response = $this->post("/markets/{$market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert
        $referral->refresh();
        $this->assertEquals('expired', $referral->status);
        
        // No bonus should be given
        $this->referrer->refresh();
        $this->assertEquals(1000000, $this->referrer->wallet_balance); // Unchanged
    }

    /** @test */
    public function referral_tracking_via_livewire_component()
    {
        // Arrange
        $this->actingAs($this->referrer);

        // Act
        Livewire::test('referral-invite-component')
            ->set('email', 'newuser@example.com')
            ->set('message', 'Join us!')
            ->call('sendInvite')
            ->assertHasNoErrors()
            ->assertEmitted('inviteSent');

        // Assert
        $this->assertDatabaseHas('referral_invites', [
            'referrer_id' => $this->referrer->id,
            'email' => 'newuser@example.com'
        ]);
    }

    /** @test */
    public function referral_creates_activity_logs()
    {
        // Arrange
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
            'status' => 'pending'
        ]);

        $market = \App\Models\Market::factory()->create(['status' => 'open']);
        $this->actingAs($this->referred);

        // Act
        $response = $this->post("/markets/{$market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->referrer->id,
            'action' => 'referral_activated',
            'description' => 'Referral activated and bonus earned'
        ]);
        
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->referred->id,
            'action' => 'referral_welcome_bonus',
            'description' => 'Welcome bonus received for being referred'
        ]);
    }

    /** @test */
    public function referral_limits_per_user()
    {
        // Arrange
        config(['app.max_referrals_per_user' => 2]);
        
        // Create 2 existing referrals
        Referral::factory(2)->create([
            'referrer_id' => $this->referrer->id,
            'status' => 'active'
        ]);

        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+2348012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'referral_code' => $this->referrer->referral_code,
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors('referral_code');
        $this->assertDatabaseMissing('referrals', [
            'referrer_id' => $this->referrer->id,
            'referred_id' => User::where('email', $userData['email'])->first()?->id
        ]);
    }

    /** @test */
    public function referral_bonus_tiers_based_on_referral_count()
    {
        // Arrange
        config([
            'app.referral_bonus_tiers' => [
                1 => 50000,  // 500 NGN for 1st referral
                5 => 100000, // 1000 NGN for 5th referral
                10 => 200000 // 2000 NGN for 10th referral
            ]
        ]);

        // Create 4 existing active referrals
        Referral::factory(4)->create([
            'referrer_id' => $this->referrer->id,
            'status' => 'active'
        ]);

        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
            'status' => 'pending'
        ]);

        $market = \App\Models\Market::factory()->create(['status' => 'open']);
        $this->actingAs($this->referred);

        // Act - This will be the 5th referral
        $response = $this->post("/markets/{$market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert - Should receive tier 2 bonus
        $this->referrer->refresh();
        $this->assertEquals(1100000, $this->referrer->wallet_balance); // Original + 1000 NGN bonus
    }

    /** @test */
    public function guest_cannot_access_referral_routes()
    {
        // Act & Assert
        $this->get('/referrals')->assertRedirect('/login');
        $this->post('/referrals/invite')->assertRedirect('/login');
        $this->get('/referrals/earnings')->assertRedirect('/login');
    }
}
