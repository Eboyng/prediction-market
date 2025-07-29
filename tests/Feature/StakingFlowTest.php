<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Market;
use App\Models\Category;
use App\Models\Stake;
use App\Models\PromoCode;
use App\Services\OddsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class StakingFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Market $market;
    protected OddsService $oddsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'wallet_balance' => 1000000, // 10,000 NGN
            'kyc_status' => 'verified'
        ]);
        
        $category = Category::factory()->create();
        $this->market = Market::factory()->create([
            'category_id' => $category->id,
            'status' => 'open',
            'closes_at' => now()->addDays(7)
        ]);
        
        $this->oddsService = new OddsService();
    }

    /** @test */
    public function user_can_place_stake_with_sufficient_balance()
    {
        // Arrange
        $this->actingAs($this->user);
        $stakeAmount = 100000; // 1,000 NGN

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('stakes', [
            'user_id' => $this->user->id,
            'market_id' => $this->market->id,
            'amount' => $stakeAmount,
            'position' => 'yes',
            'status' => 'active'
        ]);
        
        // Check wallet balance was deducted
        $this->user->refresh();
        $this->assertEquals(900000, $this->user->wallet_balance); // 9,000 NGN remaining
    }

    /** @test */
    public function user_cannot_stake_with_insufficient_balance()
    {
        // Arrange
        $this->actingAs($this->user);
        $stakeAmount = 2000000; // 20,000 NGN (more than balance)

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('stakes', [
            'user_id' => $this->user->id,
            'market_id' => $this->market->id,
            'amount' => $stakeAmount
        ]);
        
        // Check wallet balance unchanged
        $this->user->refresh();
        $this->assertEquals(1000000, $this->user->wallet_balance);
    }

    /** @test */
    public function user_cannot_stake_on_closed_market()
    {
        // Arrange
        $this->actingAs($this->user);
        $this->market->update(['status' => 'closed']);
        $stakeAmount = 100000;

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseMissing('stakes', [
            'user_id' => $this->user->id,
            'market_id' => $this->market->id
        ]);
    }

    /** @test */
    public function unverified_user_cannot_stake()
    {
        // Arrange
        $this->user->update(['kyc_status' => 'pending']);
        $this->actingAs($this->user);
        $stakeAmount = 100000;

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseMissing('stakes', [
            'user_id' => $this->user->id,
            'market_id' => $this->market->id
        ]);
    }

    /** @test */
    public function stake_calculates_payout_correctly()
    {
        // Arrange
        $this->actingAs($this->user);
        $stakeAmount = 100000;
        $expectedOdds = $this->oddsService->calculateOdds($this->market, 'yes');

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $stake = Stake::where('user_id', $this->user->id)->first();
        $expectedPayout = $this->oddsService->calculatePayout($stakeAmount, $expectedOdds['yes']);
        
        $this->assertEquals($expectedPayout, $stake->potential_payout);
        $this->assertEquals($expectedOdds['yes'], $stake->odds_at_stake);
    }

    /** @test */
    public function user_can_apply_promo_code_to_stake()
    {
        // Arrange
        $this->actingAs($this->user);
        $promoCode = PromoCode::factory()->create([
            'code' => 'BONUS10',
            'discount_percentage' => 10,
            'is_active' => true,
            'expires_at' => now()->addDays(30)
        ]);
        
        $stakeAmount = 100000;

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes',
            'promo_code' => 'BONUS10'
        ]);

        // Assert
        $stake = Stake::where('user_id', $this->user->id)->first();
        $this->assertEquals($promoCode->id, $stake->promo_code_id);
        
        // Check that payout is increased by discount
        $regularOdds = $this->oddsService->calculateOdds($this->market, 'yes');
        $regularPayout = $this->oddsService->calculatePayout($stakeAmount, $regularOdds['yes']);
        $discountedPayout = $this->oddsService->calculatePayoutWithPromo($stakeAmount, $regularOdds['yes'], 10);
        
        $this->assertEquals($discountedPayout, $stake->potential_payout);
    }

    /** @test */
    public function user_cannot_use_expired_promo_code()
    {
        // Arrange
        $this->actingAs($this->user);
        $promoCode = PromoCode::factory()->create([
            'code' => 'EXPIRED',
            'discount_percentage' => 10,
            'is_active' => true,
            'expires_at' => now()->subDays(1) // Expired
        ]);
        
        $stakeAmount = 100000;

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes',
            'promo_code' => 'EXPIRED'
        ]);

        // Assert
        $response->assertSessionHasErrors('promo_code');
        $this->assertDatabaseMissing('stakes', [
            'user_id' => $this->user->id,
            'promo_code_id' => $promoCode->id
        ]);
    }

    /** @test */
    public function stake_creates_activity_log()
    {
        // Arrange
        $this->actingAs($this->user);
        $stakeAmount = 100000;

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'stake_placed',
            'description' => "Placed stake of ₦1,000.00 on market: {$this->market->question}"
        ]);
    }

    /** @test */
    public function user_can_stake_via_livewire_component()
    {
        // Arrange
        $this->actingAs($this->user);

        // Act
        Livewire::test('stake-form-component', ['market' => $this->market])
            ->set('amount', 100000)
            ->set('position', 'yes')
            ->call('placeStake')
            ->assertHasNoErrors()
            ->assertEmitted('stakeCreated');

        // Assert
        $this->assertDatabaseHas('stakes', [
            'user_id' => $this->user->id,
            'market_id' => $this->market->id,
            'amount' => 100000,
            'position' => 'yes'
        ]);
    }

    /** @test */
    public function stake_validates_minimum_amount()
    {
        // Arrange
        $this->actingAs($this->user);
        $stakeAmount = 5000; // 50 NGN (below minimum)

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('stakes', [
            'user_id' => $this->user->id,
            'amount' => $stakeAmount
        ]);
    }

    /** @test */
    public function stake_validates_maximum_amount()
    {
        // Arrange
        $this->actingAs($this->user);
        $this->user->update(['wallet_balance' => 50000000]); // 500,000 NGN
        $stakeAmount = 10000000; // 100,000 NGN (above maximum)

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => $stakeAmount,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('stakes', [
            'user_id' => $this->user->id,
            'amount' => $stakeAmount
        ]);
    }

    /** @test */
    public function user_can_view_their_stakes()
    {
        // Arrange
        $this->actingAs($this->user);
        $stakes = Stake::factory(3)->create([
            'user_id' => $this->user->id,
            'market_id' => $this->market->id
        ]);

        // Act
        $response = $this->get('/my-stakes');

        // Assert
        $response->assertStatus(200);
        foreach ($stakes as $stake) {
            $response->assertSee($stake->amount);
            $response->assertSee($stake->position);
        }
    }

    /** @test */
    public function user_can_cancel_stake_before_market_closes()
    {
        // Arrange
        $this->actingAs($this->user);
        $stake = Stake::factory()->create([
            'user_id' => $this->user->id,
            'market_id' => $this->market->id,
            'amount' => 100000,
            'status' => 'active'
        ]);

        // Act
        $response = $this->delete("/stakes/{$stake->id}");

        // Assert
        $response->assertRedirect();
        $stake->refresh();
        $this->assertEquals('cancelled', $stake->status);
        
        // Check wallet balance was refunded
        $this->user->refresh();
        $this->assertEquals(1100000, $this->user->wallet_balance); // Original + refund
    }

    /** @test */
    public function user_cannot_cancel_stake_after_market_closes()
    {
        // Arrange
        $this->actingAs($this->user);
        $this->market->update(['status' => 'closed']);
        $stake = Stake::factory()->create([
            'user_id' => $this->user->id,
            'market_id' => $this->market->id,
            'status' => 'active'
        ]);

        // Act
        $response = $this->delete("/stakes/{$stake->id}");

        // Assert
        $response->assertStatus(403);
        $stake->refresh();
        $this->assertEquals('active', $stake->status);
    }

    /** @test */
    public function odds_update_after_stake_placement()
    {
        // Arrange
        $this->actingAs($this->user);
        $initialOdds = $this->oddsService->calculateOdds($this->market, 'yes');

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => 500000, // Large stake to affect odds
            'position' => 'yes'
        ]);

        // Assert
        $newOdds = $this->oddsService->calculateOdds($this->market, 'yes');
        $this->assertNotEquals($initialOdds['yes'], $newOdds['yes']);
        $this->assertLessThan($initialOdds['yes'], $newOdds['yes']); // Yes odds should decrease
    }

    /** @test */
    public function stake_enforces_rate_limiting()
    {
        // Arrange
        $this->actingAs($this->user);
        $stakeAmount = 50000;

        // Act - Make multiple stake attempts quickly
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post("/markets/{$this->market->id}/stake", [
                'amount' => $stakeAmount,
                'position' => 'yes'
            ]);
        }

        // Assert - Should be rate limited
        $response->assertStatus(429);
    }

    /** @test */
    public function guest_user_cannot_place_stake()
    {
        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('stakes', [
            'market_id' => $this->market->id
        ]);
    }
}
