<?php

namespace Tests\Unit;

use App\Models\PromoCode;
use App\Models\User;
use App\Models\Stake;
use App\Services\PromoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PromoService $promoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->promoService = new PromoService();
    }

    /** @test */
    public function it_can_validate_active_promo_code()
    {
        // Arrange
        $promoCode = PromoCode::factory()->create([
            'code' => 'VALID10',
            'discount_percentage' => 10,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 100,
            'used_count' => 5
        ]);

        // Act
        $result = $this->promoService->validatePromoCode('VALID10');

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEquals($promoCode->id, $result['promo_code']->id);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_rejects_expired_promo_code()
    {
        // Arrange
        PromoCode::factory()->create([
            'code' => 'EXPIRED',
            'discount_percentage' => 15,
            'is_active' => true,
            'expires_at' => Carbon::now()->subDays(1)
        ]);

        // Act
        $result = $this->promoService->validatePromoCode('EXPIRED');

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Promo code has expired', $result['errors']);
    }

    /** @test */
    public function it_rejects_inactive_promo_code()
    {
        // Arrange
        PromoCode::factory()->create([
            'code' => 'INACTIVE',
            'discount_percentage' => 20,
            'is_active' => false,
            'expires_at' => Carbon::now()->addDays(30)
        ]);

        // Act
        $result = $this->promoService->validatePromoCode('INACTIVE');

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Promo code is not active', $result['errors']);
    }

    /** @test */
    public function it_rejects_usage_limit_exceeded_promo_code()
    {
        // Arrange
        PromoCode::factory()->create([
            'code' => 'MAXED',
            'discount_percentage' => 25,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 10,
            'used_count' => 10
        ]);

        // Act
        $result = $this->promoService->validatePromoCode('MAXED');

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Promo code usage limit exceeded', $result['errors']);
    }

    /** @test */
    public function it_rejects_nonexistent_promo_code()
    {
        // Act
        $result = $this->promoService->validatePromoCode('NONEXISTENT');

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Invalid promo code', $result['errors']);
    }

    /** @test */
    public function it_calculates_discount_correctly()
    {
        // Arrange
        $promoCode = PromoCode::factory()->create([
            'discount_percentage' => 15
        ]);
        $originalAmount = 100000; // 1000 NGN

        // Act
        $discount = $this->promoService->calculateDiscount($originalAmount, $promoCode);

        // Assert
        $expectedDiscount = $originalAmount * 0.15; // 15% of 1000 NGN = 150 NGN
        $this->assertEquals($expectedDiscount, $discount);
    }

    /** @test */
    public function it_applies_promo_code_to_stake()
    {
        // Arrange
        $user = User::factory()->create();
        $promoCode = PromoCode::factory()->create([
            'code' => 'STAKE20',
            'discount_percentage' => 20,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30)
        ]);
        $stakeAmount = 200000; // 2000 NGN

        // Act
        $result = $this->promoService->applyPromoCodeToStake($user, 'STAKE20', $stakeAmount);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(40000, $result['discount_amount']); // 20% of 2000 NGN
        $this->assertEquals(240000, $result['final_payout']); // Original + discount
        $this->assertEquals($promoCode->id, $result['promo_code_id']);
    }

    /** @test */
    public function it_tracks_promo_code_usage()
    {
        // Arrange
        $user = User::factory()->create();
        $promoCode = PromoCode::factory()->create([
            'code' => 'TRACK10',
            'discount_percentage' => 10,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30),
            'used_count' => 5
        ]);

        // Act
        $this->promoService->recordPromoCodeUsage($promoCode, $user, 100000);

        // Assert
        $promoCode->refresh();
        $this->assertEquals(6, $promoCode->used_count);
        
        $this->assertDatabaseHas('promo_code_usages', [
            'promo_code_id' => $promoCode->id,
            'user_id' => $user->id,
            'amount_saved' => 10000 // 10% of 100000
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_usage_by_same_user()
    {
        // Arrange
        $user = User::factory()->create();
        $promoCode = PromoCode::factory()->create([
            'code' => 'ONETIME',
            'discount_percentage' => 30,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30),
            'max_uses_per_user' => 1
        ]);

        // First usage
        $this->promoService->recordPromoCodeUsage($promoCode, $user, 100000);

        // Act - Second usage attempt
        $result = $this->promoService->validatePromoCodeForUser('ONETIME', $user);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('You have already used this promo code', $result['errors']);
    }

    /** @test */
    public function it_handles_minimum_stake_requirements()
    {
        // Arrange
        $promoCode = PromoCode::factory()->create([
            'code' => 'BIGSTAKE',
            'discount_percentage' => 25,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30),
            'minimum_stake_amount' => 500000 // 5000 NGN minimum
        ]);
        $smallStake = 200000; // 2000 NGN

        // Act
        $result = $this->promoService->validatePromoCodeForAmount('BIGSTAKE', $smallStake);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Minimum stake amount not met', $result['errors']);
    }

    /** @test */
    public function it_handles_maximum_discount_limits()
    {
        // Arrange
        $promoCode = PromoCode::factory()->create([
            'code' => 'CAPPED',
            'discount_percentage' => 50,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30),
            'max_discount_amount' => 50000 // Max 500 NGN discount
        ]);
        $largeStake = 200000; // 2000 NGN

        // Act
        $discount = $this->promoService->calculateDiscount($largeStake, $promoCode);

        // Assert
        $this->assertEquals(50000, $discount); // Capped at 500 NGN, not 50% of 2000 NGN
    }

    /** @test */
    public function it_generates_unique_promo_codes()
    {
        // Act
        $code1 = $this->promoService->generateUniqueCode();
        $code2 = $this->promoService->generateUniqueCode();

        // Assert
        $this->assertNotEquals($code1, $code2);
        $this->assertEquals(8, strlen($code1));
        $this->assertEquals(8, strlen($code2));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $code1);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $code2);
    }

    /** @test */
    public function it_creates_campaign_promo_codes()
    {
        // Arrange
        $campaignData = [
            'name' => 'Summer Sale',
            'discount_percentage' => 20,
            'quantity' => 100,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 1000
        ];

        // Act
        $promoCodes = $this->promoService->createCampaignPromoCodes($campaignData);

        // Assert
        $this->assertCount(100, $promoCodes);
        $this->assertDatabaseCount('promo_codes', 100);
        
        foreach ($promoCodes as $promoCode) {
            $this->assertEquals(20, $promoCode->discount_percentage);
            $this->assertTrue($promoCode->is_active);
            $this->assertEquals(1000, $promoCode->usage_limit);
        }
    }

    /** @test */
    public function it_gets_user_promo_history()
    {
        // Arrange
        $user = User::factory()->create();
        $promoCodes = PromoCode::factory(3)->create();
        
        foreach ($promoCodes as $promoCode) {
            $this->promoService->recordPromoCodeUsage($promoCode, $user, 100000);
        }

        // Act
        $history = $this->promoService->getUserPromoHistory($user);

        // Assert
        $this->assertCount(3, $history);
        foreach ($history as $usage) {
            $this->assertEquals($user->id, $usage->user_id);
            $this->assertEquals(10000, $usage->amount_saved); // Assuming 10% discount
        }
    }

    /** @test */
    public function it_calculates_total_savings_for_user()
    {
        // Arrange
        $user = User::factory()->create();
        $promoCodes = PromoCode::factory(2)->create(['discount_percentage' => 15]);
        
        foreach ($promoCodes as $promoCode) {
            $this->promoService->recordPromoCodeUsage($promoCode, $user, 200000); // 2000 NGN each
        }

        // Act
        $totalSavings = $this->promoService->getUserTotalSavings($user);

        // Assert
        $expectedSavings = 2 * (200000 * 0.15); // 2 * 300 NGN = 600 NGN
        $this->assertEquals($expectedSavings, $totalSavings);
    }

    /** @test */
    public function it_deactivates_expired_promo_codes()
    {
        // Arrange
        $expiredCodes = PromoCode::factory(3)->create([
            'is_active' => true,
            'expires_at' => Carbon::now()->subDays(1)
        ]);
        $activeCodes = PromoCode::factory(2)->create([
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(30)
        ]);

        // Act
        $deactivatedCount = $this->promoService->deactivateExpiredPromoCodes();

        // Assert
        $this->assertEquals(3, $deactivatedCount);
        
        foreach ($expiredCodes as $code) {
            $code->refresh();
            $this->assertFalse($code->is_active);
        }
        
        foreach ($activeCodes as $code) {
            $code->refresh();
            $this->assertTrue($code->is_active);
        }
    }

    /** @test */
    public function it_handles_percentage_vs_fixed_amount_discounts()
    {
        // Arrange
        $percentagePromo = PromoCode::factory()->create([
            'discount_percentage' => 20,
            'discount_amount' => null
        ]);
        $fixedPromo = PromoCode::factory()->create([
            'discount_percentage' => null,
            'discount_amount' => 50000 // Fixed 500 NGN
        ]);
        $stakeAmount = 300000; // 3000 NGN

        // Act
        $percentageDiscount = $this->promoService->calculateDiscount($stakeAmount, $percentagePromo);
        $fixedDiscount = $this->promoService->calculateDiscount($stakeAmount, $fixedPromo);

        // Assert
        $this->assertEquals(60000, $percentageDiscount); // 20% of 3000 NGN
        $this->assertEquals(50000, $fixedDiscount); // Fixed 500 NGN
    }
}
