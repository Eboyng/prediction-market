<?php

namespace Tests\Unit;

use App\Models\Market;
use App\Models\Stake;
use App\Services\OddsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OddsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OddsService $oddsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oddsService = new OddsService();
    }

    /** @test */
    public function it_calculates_initial_odds_correctly()
    {
        // Arrange
        $market = Market::factory()->create([
            'initial_yes_odds' => 0.5,
            'initial_no_odds' => 0.5
        ]);

        // Act
        $odds = $this->oddsService->calculateOdds($market, 'yes');

        // Assert
        $this->assertIsArray($odds);
        $this->assertArrayHasKey('yes', $odds);
        $this->assertArrayHasKey('no', $odds);
        $this->assertEquals(0.5, $odds['yes']);
        $this->assertEquals(0.5, $odds['no']);
    }

    /** @test */
    public function it_updates_odds_based_on_stakes()
    {
        // Arrange
        $market = Market::factory()->create([
            'initial_yes_odds' => 0.5,
            'initial_no_odds' => 0.5
        ]);

        // Create stakes heavily favoring 'yes'
        Stake::factory(10)->create([
            'market_id' => $market->id,
            'position' => 'yes',
            'amount' => 100000 // 1000 NGN each
        ]);

        Stake::factory(2)->create([
            'market_id' => $market->id,
            'position' => 'no',
            'amount' => 100000 // 1000 NGN each
        ]);

        // Act
        $odds = $this->oddsService->calculateOdds($market, 'yes');

        // Assert
        $this->assertLessThan(0.5, $odds['yes']); // Yes odds should decrease (more likely)
        $this->assertGreaterThan(0.5, $odds['no']); // No odds should increase (less likely)
        $this->assertEqualsWithDelta(1.0, $odds['yes'] + $odds['no'], 0.01); // Should sum to ~1
    }

    /** @test */
    public function it_applies_liquidity_parameter()
    {
        // Arrange
        $market = Market::factory()->create([
            'initial_yes_odds' => 0.5,
            'initial_no_odds' => 0.5,
            'liquidity_parameter' => 1000 // Higher liquidity = slower odds changes
        ]);

        Stake::factory(5)->create([
            'market_id' => $market->id,
            'position' => 'yes',
            'amount' => 100000
        ]);

        // Act
        $oddsHighLiquidity = $this->oddsService->calculateOdds($market, 'yes');

        // Update market with lower liquidity
        $market->update(['liquidity_parameter' => 100]);
        $oddsLowLiquidity = $this->oddsService->calculateOdds($market, 'yes');

        // Assert
        $this->assertGreaterThan($oddsLowLiquidity['yes'], $oddsHighLiquidity['yes']);
        // Higher liquidity should result in smaller odds changes
    }

    /** @test */
    public function it_calculates_payout_correctly()
    {
        // Arrange
        $market = Market::factory()->create();
        $stakeAmount = 100000; // 1000 NGN
        $odds = 0.3; // 30% probability = 3.33x payout

        // Act
        $payout = $this->oddsService->calculatePayout($stakeAmount, $odds);

        // Assert
        $expectedPayout = $stakeAmount / $odds;
        $this->assertEquals($expectedPayout, $payout);
        $this->assertGreaterThan($stakeAmount, $payout); // Payout should be more than stake
    }

    /** @test */
    public function it_handles_extreme_odds_safely()
    {
        // Arrange
        $market = Market::factory()->create();
        $stakeAmount = 100000;

        // Act & Assert - Very low odds
        $lowOddsPayout = $this->oddsService->calculatePayout($stakeAmount, 0.01);
        $this->assertIsFloat($lowOddsPayout);
        $this->assertGreaterThan(0, $lowOddsPayout);

        // Act & Assert - Very high odds
        $highOddsPayout = $this->oddsService->calculatePayout($stakeAmount, 0.99);
        $this->assertIsFloat($highOddsPayout);
        $this->assertGreaterThan($stakeAmount, $highOddsPayout);
    }

    /** @test */
    public function it_applies_promo_discount()
    {
        // Arrange
        $market = Market::factory()->create();
        $stakeAmount = 100000; // 1000 NGN
        $odds = 0.5;
        $discountPercentage = 10; // 10% discount

        // Act
        $regularPayout = $this->oddsService->calculatePayout($stakeAmount, $odds);
        $discountedPayout = $this->oddsService->calculatePayoutWithPromo(
            $stakeAmount, 
            $odds, 
            $discountPercentage
        );

        // Assert
        $this->assertGreaterThan($regularPayout, $discountedPayout);
        $expectedDiscount = $regularPayout * ($discountPercentage / 100);
        $this->assertEquals($regularPayout + $expectedDiscount, $discountedPayout);
    }

    /** @test */
    public function it_validates_odds_boundaries()
    {
        // Arrange
        $market = Market::factory()->create();

        // Create extreme stakes to test boundaries
        Stake::factory(100)->create([
            'market_id' => $market->id,
            'position' => 'yes',
            'amount' => 1000000 // 10,000 NGN each
        ]);

        // Act
        $odds = $this->oddsService->calculateOdds($market, 'yes');

        // Assert
        $this->assertGreaterThan(0.01, $odds['yes']); // Minimum odds boundary
        $this->assertLessThan(0.99, $odds['yes']); // Maximum odds boundary
        $this->assertGreaterThan(0.01, $odds['no']);
        $this->assertLessThan(0.99, $odds['no']);
    }

    /** @test */
    public function it_handles_no_stakes_scenario()
    {
        // Arrange
        $market = Market::factory()->create([
            'initial_yes_odds' => 0.6,
            'initial_no_odds' => 0.4
        ]);

        // Act - No stakes placed
        $odds = $this->oddsService->calculateOdds($market, 'yes');

        // Assert
        $this->assertEquals(0.6, $odds['yes']);
        $this->assertEquals(0.4, $odds['no']);
    }

    /** @test */
    public function it_calculates_market_depth()
    {
        // Arrange
        $market = Market::factory()->create();
        
        Stake::factory(5)->create([
            'market_id' => $market->id,
            'position' => 'yes',
            'amount' => 200000 // 2000 NGN each
        ]);

        Stake::factory(3)->create([
            'market_id' => $market->id,
            'position' => 'no',
            'amount' => 150000 // 1500 NGN each
        ]);

        // Act
        $depth = $this->oddsService->getMarketDepth($market);

        // Assert
        $this->assertIsArray($depth);
        $this->assertArrayHasKey('yes_volume', $depth);
        $this->assertArrayHasKey('no_volume', $depth);
        $this->assertArrayHasKey('total_volume', $depth);
        $this->assertArrayHasKey('yes_percentage', $depth);
        $this->assertArrayHasKey('no_percentage', $depth);
        
        $this->assertEquals(1000000, $depth['yes_volume']); // 10,000 NGN
        $this->assertEquals(450000, $depth['no_volume']); // 4,500 NGN
        $this->assertEquals(1450000, $depth['total_volume']); // 14,500 NGN
    }

    /** @test */
    public function it_calculates_implied_probability()
    {
        // Arrange
        $odds = 0.25; // 25% odds

        // Act
        $probability = $this->oddsService->getImpliedProbability($odds);

        // Assert
        $this->assertEquals(25, $probability); // Should return percentage
    }

    /** @test */
    public function it_calculates_kelly_criterion()
    {
        // Arrange
        $userOdds = 0.6; // User believes 60% chance
        $marketOdds = 0.4; // Market shows 40% chance
        $bankroll = 1000000; // 10,000 NGN

        // Act
        $kellyBet = $this->oddsService->calculateKellyCriterion($userOdds, $marketOdds, $bankroll);

        // Assert
        $this->assertIsFloat($kellyBet);
        $this->assertGreaterThan(0, $kellyBet);
        $this->assertLessThanOrEqual($bankroll, $kellyBet);
    }

    /** @test */
    public function it_handles_arbitrage_detection()
    {
        // Arrange
        $market = Market::factory()->create();
        
        // Create unbalanced stakes that might create arbitrage
        Stake::factory(20)->create([
            'market_id' => $market->id,
            'position' => 'yes',
            'amount' => 50000
        ]);

        // Act
        $arbitrageOpportunity = $this->oddsService->detectArbitrage($market);

        // Assert
        $this->assertIsBool($arbitrageOpportunity);
    }

    /** @test */
    public function it_calculates_expected_value()
    {
        // Arrange
        $stakeAmount = 100000; // 1000 NGN
        $winProbability = 0.6; // 60% chance to win
        $payout = 200000; // 2000 NGN payout

        // Act
        $expectedValue = $this->oddsService->calculateExpectedValue($stakeAmount, $winProbability, $payout);

        // Assert
        $expectedWin = $winProbability * $payout;
        $expectedLoss = (1 - $winProbability) * $stakeAmount;
        $this->assertEquals($expectedWin - $expectedLoss, $expectedValue);
    }
}
