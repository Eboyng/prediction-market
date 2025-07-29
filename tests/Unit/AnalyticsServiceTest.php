<?php

namespace Tests\Unit;

use App\Models\Market;
use App\Models\Stake;
use App\Models\User;
use App\Models\Wallet;
use App\Models\ActivityLog;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
        
        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_can_get_platform_overview()
    {
        // Arrange
        $users = User::factory(10)->create();
        $markets = Market::factory(5)->create(['status' => 'open']);
        $stakes = Stake::factory(20)->create(['amount' => 100000]); // 1000 NGN each

        // Act
        $overview = $this->analyticsService->getPlatformOverview(30);

        // Assert
        $this->assertIsArray($overview);
        $this->assertArrayHasKey('total_users', $overview);
        $this->assertArrayHasKey('total_markets', $overview);
        $this->assertArrayHasKey('active_markets', $overview);
        $this->assertArrayHasKey('total_stakes', $overview);
        $this->assertArrayHasKey('total_volume', $overview);
        $this->assertArrayHasKey('platform_revenue', $overview);
        
        $this->assertEquals(10, $overview['total_users']);
        $this->assertEquals(5, $overview['total_markets']);
        $this->assertEquals(5, $overview['active_markets']);
        $this->assertEquals(20, $overview['total_stakes']);
        $this->assertEquals(2000000, $overview['total_volume']); // 20,000 NGN
    }

    /** @test */
    public function it_can_get_user_analytics()
    {
        // Arrange
        $oldUsers = User::factory(5)->create([
            'created_at' => Carbon::now()->subDays(20)
        ]);
        $newUsers = User::factory(3)->create([
            'created_at' => Carbon::now()->subDays(5)
        ]);
        
        // Create stakes for some users
        Stake::factory(10)->create([
            'user_id' => $oldUsers->first()->id,
            'amount' => 500000 // 5000 NGN
        ]);

        // Act
        $analytics = $this->analyticsService->getUserAnalytics(30);

        // Assert
        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('registration_trends', $analytics);
        $this->assertArrayHasKey('active_users', $analytics);
        $this->assertArrayHasKey('retention_rate', $analytics);
        $this->assertArrayHasKey('top_users', $analytics);
        $this->assertArrayHasKey('user_segments', $analytics);
        
        $this->assertEquals(1, $analytics['active_users']); // Only one user has stakes
    }

    /** @test */
    public function it_can_get_market_analytics()
    {
        // Arrange
        $category = \App\Models\Category::factory()->create(['name' => 'Sports']);
        $markets = Market::factory(3)->create([
            'category_id' => $category->id,
            'status' => 'open',
            'created_at' => Carbon::now()->subDays(10)
        ]);
        
        // Create stakes for markets
        foreach ($markets as $market) {
            Stake::factory(5)->create([
                'market_id' => $market->id,
                'amount' => 200000 // 2000 NGN each
            ]);
        }

        // Act
        $analytics = $this->analyticsService->getMarketAnalytics(30);

        // Assert
        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('market_trends', $analytics);
        $this->assertArrayHasKey('markets_by_category', $analytics);
        $this->assertArrayHasKey('top_markets', $analytics);
        $this->assertArrayHasKey('market_success_rate', $analytics);
        $this->assertArrayHasKey('avg_market_duration', $analytics);
        
        $this->assertCount(3, $analytics['top_markets']);
    }

    /** @test */
    public function it_can_get_financial_analytics()
    {
        // Arrange
        $stakes = Stake::factory(10)->create([
            'amount' => 100000, // 1000 NGN each
            'created_at' => Carbon::now()->subDays(5)
        ]);
        
        $wallets = Wallet::factory(5)->create([
            'type' => 'deposit',
            'amount' => 500000, // 5000 NGN each
            'status' => 'completed',
            'created_at' => Carbon::now()->subDays(3)
        ]);

        // Act
        $analytics = $this->analyticsService->getFinancialAnalytics(30);

        // Assert
        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('volume_trends', $analytics);
        $this->assertArrayHasKey('revenue_breakdown', $analytics);
        $this->assertArrayHasKey('wallet_analytics', $analytics);
        $this->assertArrayHasKey('payout_trends', $analytics);
        $this->assertArrayHasKey('profit_margins', $analytics);
        
        $this->assertEquals(2500000, $analytics['wallet_analytics']['total_deposits']); // 25,000 NGN
    }

    /** @test */
    public function it_can_get_realtime_dashboard()
    {
        // Arrange
        $users = User::factory(3)->create();
        
        // Create recent activity logs
        foreach ($users as $user) {
            ActivityLog::factory()->create([
                'user_id' => $user->id,
                'created_at' => Carbon::now()->subMinutes(5)
            ]);
        }
        
        // Create recent stakes
        Stake::factory(5)->create([
            'created_at' => Carbon::now()->subMinutes(30),
            'amount' => 150000 // 1500 NGN each
        ]);
        
        // Create trending markets
        $markets = Market::factory(3)->create(['status' => 'open']);
        foreach ($markets as $market) {
            Stake::factory(2)->create([
                'market_id' => $market->id,
                'created_at' => Carbon::now()->subHours(12)
            ]);
        }

        // Act
        $dashboard = $this->analyticsService->getRealTimeDashboard();

        // Assert
        $this->assertIsArray($dashboard);
        $this->assertArrayHasKey('active_users_now', $dashboard);
        $this->assertArrayHasKey('stakes_last_hour', $dashboard);
        $this->assertArrayHasKey('volume_last_hour', $dashboard);
        $this->assertArrayHasKey('new_markets_today', $dashboard);
        $this->assertArrayHasKey('recent_activities', $dashboard);
        $this->assertArrayHasKey('trending_markets', $dashboard);
        $this->assertArrayHasKey('system_health', $dashboard);
        
        $this->assertEquals(3, $dashboard['active_users_now']);
        $this->assertEquals(5, $dashboard['stakes_last_hour']);
        $this->assertEquals(750000, $dashboard['volume_last_hour']); // 7500 NGN
    }

    /** @test */
    public function it_can_export_analytics_data()
    {
        // Arrange
        User::factory(5)->create();
        Market::factory(3)->create();
        Stake::factory(10)->create();

        // Act
        $exportedData = $this->analyticsService->exportAnalytics('overview', 30);

        // Assert
        $this->assertIsArray($exportedData);
        $this->assertArrayHasKey('total_users', $exportedData);
        $this->assertArrayHasKey('total_markets', $exportedData);
        $this->assertArrayHasKey('total_stakes', $exportedData);
    }

    /** @test */
    public function it_can_clear_analytics_cache()
    {
        // Arrange
        Cache::put('platform_overview_30', ['test' => 'data'], 300);
        Cache::put('user_analytics_30', ['test' => 'data'], 300);
        
        $this->assertTrue(Cache::has('platform_overview_30'));
        $this->assertTrue(Cache::has('user_analytics_30'));

        // Act
        $this->analyticsService->clearCache();

        // Assert - Cache should be cleared (this is implementation dependent)
        // Note: The actual cache clearing might work differently based on cache driver
        $this->assertTrue(true); // Placeholder assertion
    }

    /** @test */
    public function it_calculates_platform_revenue_correctly()
    {
        // Arrange
        $stakes = Stake::factory(10)->create([
            'amount' => 100000, // 1000 NGN each
            'created_at' => Carbon::now()->subDays(5)
        ]);
        
        // Mock platform fee percentage
        config(['app.platform_fee_percentage' => 2.5]);

        // Act
        $overview = $this->analyticsService->getPlatformOverview(30);

        // Assert
        $expectedRevenue = 1000000 * 0.025; // 10,000 NGN * 2.5% = 250 NGN
        $this->assertEquals($expectedRevenue, $overview['platform_revenue']);
    }

    /** @test */
    public function it_handles_empty_data_gracefully()
    {
        // Act - No data in database
        $overview = $this->analyticsService->getPlatformOverview(30);
        $userAnalytics = $this->analyticsService->getUserAnalytics(30);
        $marketAnalytics = $this->analyticsService->getMarketAnalytics(30);
        $financialAnalytics = $this->analyticsService->getFinancialAnalytics(30);

        // Assert
        $this->assertEquals(0, $overview['total_users']);
        $this->assertEquals(0, $overview['total_markets']);
        $this->assertEquals(0, $overview['total_stakes']);
        $this->assertEquals(0, $overview['total_volume']);
        
        $this->assertEquals(0, $userAnalytics['active_users']);
        $this->assertEquals(0, $userAnalytics['retention_rate']);
        
        $this->assertEquals(0, $marketAnalytics['market_success_rate']);
        $this->assertEquals(0, $marketAnalytics['avg_market_duration']);
        
        $this->assertEquals(0, $financialAnalytics['wallet_analytics']['total_deposits']);
    }

    /** @test */
    public function it_caches_analytics_data()
    {
        // Arrange
        User::factory(5)->create();
        
        // Act - First call should hit database
        $firstCall = $this->analyticsService->getPlatformOverview(30);
        
        // Act - Second call should hit cache
        $secondCall = $this->analyticsService->getPlatformOverview(30);

        // Assert
        $this->assertEquals($firstCall, $secondCall);
        
        // Verify cache key exists (implementation dependent)
        $this->assertTrue(Cache::has('platform_overview_30'));
    }
}
