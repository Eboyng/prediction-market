<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Market;
use App\Models\Category;
use App\Models\Stake;
use App\Jobs\SettlementJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MarketFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Category $category;
    protected Market $market;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'wallet_balance' => 2000000, // 20,000 NGN
            'kyc_status' => 'verified'
        ]);
        
        $this->category = Category::factory()->create(['name' => 'Sports']);
        $this->market = Market::factory()->create([
            'category_id' => $this->category->id,
            'status' => 'open',
            'closes_at' => now()->addDays(7)
        ]);
    }

    /** @test */
    public function user_can_view_market_details()
    {
        // Arrange
        $this->actingAs($this->user);

        // Act
        $response = $this->get("/markets/{$this->market->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertSee($this->market->question);
        $response->assertSee($this->market->description);
        $response->assertSee($this->category->name);
    }

    /** @test */
    public function user_can_browse_markets_by_category()
    {
        // Arrange
        $sportsMarkets = Market::factory(3)->create([
            'category_id' => $this->category->id,
            'status' => 'open'
        ]);
        
        $politicsCategory = Category::factory()->create(['name' => 'Politics']);
        $politicsMarkets = Market::factory(2)->create([
            'category_id' => $politicsCategory->id,
            'status' => 'open'
        ]);

        // Act
        $response = $this->get("/markets?category={$this->category->id}");

        // Assert
        $response->assertStatus(200);
        foreach ($sportsMarkets as $market) {
            $response->assertSee($market->question);
        }
        
        // Should not see politics markets
        foreach ($politicsMarkets as $market) {
            $response->assertDontSee($market->question);
        }
    }

    /** @test */
    public function user_can_search_markets()
    {
        // Arrange
        $searchableMarket = Market::factory()->create([
            'question' => 'Will Bitcoin reach $100,000 by end of year?',
            'status' => 'open'
        ]);
        
        $otherMarket = Market::factory()->create([
            'question' => 'Will it rain tomorrow?',
            'status' => 'open'
        ]);

        // Act
        $response = $this->get('/markets?search=Bitcoin');

        // Assert
        $response->assertStatus(200);
        $response->assertSee($searchableMarket->question);
        $response->assertDontSee($otherMarket->question);
    }

    /** @test */
    public function market_automatically_closes_at_scheduled_time()
    {
        // Arrange
        Queue::fake();
        $market = Market::factory()->create([
            'status' => 'open',
            'closes_at' => now()->addMinutes(5)
        ]);

        // Act - Simulate time passing
        $this->travel(6)->minutes();
        
        // Trigger the scheduled job that would normally run
        $this->artisan('schedule:run');

        // Assert
        Queue::assertPushed(SettlementJob::class, function ($job) use ($market) {
            return $job->market->id === $market->id;
        });
    }

    /** @test */
    public function admin_can_manually_close_market()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/markets/{$this->market->id}/close");

        // Assert
        $response->assertRedirect();
        $this->market->refresh();
        $this->assertEquals('closed', $this->market->status);
        $this->assertNotNull($this->market->closed_at);
    }

    /** @test */
    public function admin_can_settle_market_with_outcome()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $this->market->update(['status' => 'closed']);
        
        // Create stakes on both sides
        $yesStakes = Stake::factory(3)->create([
            'market_id' => $this->market->id,
            'position' => 'yes',
            'amount' => 100000,
            'status' => 'active'
        ]);
        
        $noStakes = Stake::factory(2)->create([
            'market_id' => $this->market->id,
            'position' => 'no',
            'amount' => 100000,
            'status' => 'active'
        ]);
        
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/markets/{$this->market->id}/settle", [
            'outcome' => 'yes',
            'notes' => 'Event occurred as predicted'
        ]);

        // Assert
        $response->assertRedirect();
        $this->market->refresh();
        $this->assertEquals('settled', $this->market->status);
        $this->assertEquals('yes', $this->market->outcome);
        
        // Check winning stakes are marked as won
        foreach ($yesStakes as $stake) {
            $stake->refresh();
            $this->assertEquals('won', $stake->status);
            $this->assertGreaterThan(0, $stake->payout_amount);
        }
        
        // Check losing stakes are marked as lost
        foreach ($noStakes as $stake) {
            $stake->refresh();
            $this->assertEquals('lost', $stake->status);
            $this->assertEquals(0, $stake->payout_amount);
        }
    }

    /** @test */
    public function market_can_be_cancelled_and_refunds_issued()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $this->market->update(['status' => 'closed']);
        
        $stakes = Stake::factory(3)->create([
            'market_id' => $this->market->id,
            'amount' => 100000,
            'status' => 'active'
        ]);
        
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/markets/{$this->market->id}/cancel", [
            'reason' => 'Event was cancelled due to unforeseen circumstances'
        ]);

        // Assert
        $response->assertRedirect();
        $this->market->refresh();
        $this->assertEquals('cancelled', $this->market->status);
        
        // Check all stakes are refunded
        foreach ($stakes as $stake) {
            $stake->refresh();
            $this->assertEquals('refunded', $stake->status);
            
            // Check user received refund
            $user = $stake->user;
            $this->assertDatabaseHas('wallets', [
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $stake->amount,
                'status' => 'completed'
            ]);
        }
    }

    /** @test */
    public function market_displays_current_odds()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Create stakes to influence odds
        Stake::factory(5)->create([
            'market_id' => $this->market->id,
            'position' => 'yes',
            'amount' => 200000
        ]);

        // Act
        $response = $this->get("/markets/{$this->market->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertSee('odds'); // Should display odds information
    }

    /** @test */
    public function market_shows_volume_and_participation_stats()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Create stakes for statistics
        Stake::factory(10)->create([
            'market_id' => $this->market->id,
            'amount' => 150000
        ]);

        // Act
        $response = $this->get("/markets/{$this->market->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertSee('1,500,000'); // Total volume (10 * 150,000)
        $response->assertSee('10'); // Number of stakes
    }

    /** @test */
    public function user_can_view_market_activity_feed()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Create recent stakes
        $recentStakes = Stake::factory(3)->create([
            'market_id' => $this->market->id,
            'created_at' => now()->subMinutes(5)
        ]);

        // Act
        $response = $this->get("/markets/{$this->market->id}/activity");

        // Assert
        $response->assertStatus(200);
        foreach ($recentStakes as $stake) {
            $response->assertSee($stake->position);
            $response->assertSee(number_format($stake->amount / 100, 2));
        }
    }

    /** @test */
    public function market_enforces_closing_time()
    {
        // Arrange
        $this->actingAs($this->user);
        $this->market->update([
            'closes_at' => now()->subHour(),
            'status' => 'closed'
        ]);

        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseMissing('stakes', [
            'market_id' => $this->market->id,
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function market_creation_requires_admin_role()
    {
        // Arrange
        $this->actingAs($this->user); // Regular user

        // Act
        $response = $this->post('/admin/markets', [
            'question' => 'Will it snow tomorrow?',
            'description' => 'Weather prediction market',
            'category_id' => $this->category->id,
            'closes_at' => now()->addDays(1)
        ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_new_market()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Act
        $response = $this->post('/admin/markets', [
            'question' => 'Will Bitcoin reach $50,000 this month?',
            'description' => 'Cryptocurrency price prediction',
            'category_id' => $this->category->id,
            'closes_at' => now()->addDays(30),
            'initial_yes_odds' => 0.6,
            'initial_no_odds' => 0.4
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('markets', [
            'question' => 'Will Bitcoin reach $50,000 this month?',
            'category_id' => $this->category->id,
            'status' => 'open'
        ]);
    }

    /** @test */
    public function market_validates_closing_time_in_future()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Act
        $response = $this->post('/admin/markets', [
            'question' => 'Past event market?',
            'description' => 'This should fail',
            'category_id' => $this->category->id,
            'closes_at' => now()->subDay() // Past date
        ]);

        // Assert
        $response->assertSessionHasErrors('closes_at');
    }

    /** @test */
    public function market_settlement_creates_activity_logs()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $this->market->update(['status' => 'closed']);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/markets/{$this->market->id}/settle", [
            'outcome' => 'yes',
            'notes' => 'Event confirmed'
        ]);

        // Assert
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'market_settled',
            'description' => "Market settled with outcome: yes"
        ]);
    }

    /** @test */
    public function guest_can_view_public_markets()
    {
        // Act
        $response = $this->get('/markets');

        // Assert
        $response->assertStatus(200);
        $response->assertSee($this->market->question);
    }

    /** @test */
    public function guest_cannot_place_stakes()
    {
        // Act
        $response = $this->post("/markets/{$this->market->id}/stake", [
            'amount' => 100000,
            'position' => 'yes'
        ]);

        // Assert
        $response->assertRedirect('/login');
    }
}
