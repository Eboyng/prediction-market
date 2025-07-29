<?php

namespace App\Livewire;

use App\Models\Market;
use App\Models\Category;
use App\Models\User;
use App\Models\Stake;
use App\Services\OddsService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.guest')]
#[Title('PredictNaira - Nigeria\'s Premier Prediction Market')]
class LandingPageComponent extends Component
{
    public $featuredMarkets = [];
    public $trendingMarkets = [];
    public $categories = [];
    public $platformStats = [];
    public $selectedCategory = null;
    public $showLoginModal = false;
    public $selectedMarketForBet = null;

    protected OddsService $oddsService;

    public function boot(OddsService $oddsService)
    {
        $this->oddsService = $oddsService;
    }

    public function mount()
    {
        $this->loadLandingData();
    }

    public function loadLandingData()
    {
        // Featured markets (high volume, recently created)
        $this->featuredMarkets = Market::with(['category', 'stakes'])
            ->where('status', 'open')
            ->where('closes_at', '>', now())
            ->withCount('stakes')
            ->withSum('stakes as stakes_sum_amount', 'amount')
            ->orderByDesc('stakes_sum_amount')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(function ($market) {
                // Calculate odds for both sides and store them
                $yesOdds = $this->oddsService->calculateOdds($market, 'yes');
                $noOdds = $this->oddsService->calculateOdds($market, 'no');
                $market->current_odds = [
                    'yes' => $yesOdds,
                    'no' => $noOdds
                ];
                $market->total_volume = $market->stakes_sum_amount ?? 0;
                $market->participant_count = $market->stakes_count ?? 0;
                return $market;
            });

        // Trending markets (most activity in last 24h)
        $this->trendingMarkets = Market::with(['category'])
            ->where('status', 'open')
            ->where('closes_at', '>', now())
            ->whereHas('stakes', function ($query) {
                $query->where('created_at', '>=', now()->subDay());
            })
            ->withCount(['stakes' => function ($query) {
                $query->where('created_at', '>=', now()->subDay());
            }])
            ->orderByDesc('stakes_count')
            ->limit(8)
            ->get();

        // Categories with market counts
        $this->categories = Category::withCount(['markets' => function ($query) {
            $query->where('status', 'open');
        }])
        ->whereHas('markets', function ($query) {
            $query->where('status', 'open');
        })
        ->orderByDesc('markets_count')
        ->get();

        // Platform statistics
        $this->platformStats = Cache::remember('landing_stats', 300, function () {
            return [
                'total_users' => User::count(),
                'active_markets' => Market::where('status', 'open')->count(),
                'total_volume' => Stake::sum('amount'),
                'total_payouts' => Stake::where('status', 'won')->sum('payout_amount'),
                'markets_settled' => Market::where('status', 'settled')->count(),
            ];
        });
    }

    public function filterByCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
        
        if ($categoryId) {
            $this->featuredMarkets = Market::with(['category', 'stakes'])
                ->where('status', 'open')
                ->where('category_id', $categoryId)
                ->where('closes_at', '>', now())
                ->withCount('stakes')
                ->withSum('stakes as stakes_sum_amount', 'amount')
                ->orderByDesc('stakes_sum_amount')
                ->limit(6)
                ->get()
                ->map(function ($market) {
                    // Calculate odds for both sides and store them
                    $yesOdds = $this->oddsService->calculateOdds($market, 'yes');
                    $noOdds = $this->oddsService->calculateOdds($market, 'no');
                    $market->current_odds = [
                        'yes' => $yesOdds,
                        'no' => $noOdds
                    ];
                    $market->total_volume = $market->stakes_sum_amount ?? 0;
                    $market->participant_count = $market->stakes_count ?? 0;
                    return $market;
                });
        } else {
            $this->loadLandingData();
        }
    }

    public function attemptToBet($marketId)
    {
        if (!Auth::check()) {
            $this->selectedMarketForBet = $marketId;
            $this->showLoginModal = true;
            $this->dispatch('show-login-modal');
            return;
        }

        // Redirect to market detail page for authenticated users
        return redirect()->route('markets.show', $marketId);
    }

    public function closeLoginModal()
    {
        $this->showLoginModal = false;
        $this->selectedMarketForBet = null;
    }

    public function getTimeRemaining($closesAt)
    {
        $now = now();
        $closes = \Carbon\Carbon::parse($closesAt);
        
        if ($closes->isPast()) {
            return 'Closed';
        }
        
        $diff = $now->diff($closes);
        
        if ($diff->days > 0) {
            return $diff->days . 'd ' . $diff->h . 'h';
        } elseif ($diff->h > 0) {
            return $diff->h . 'h ' . $diff->i . 'm';
        } else {
            return $diff->i . 'm ' . $diff->s . 's';
        }
    }

    public function formatCurrency($amount)
    {
        return '₦' . number_format($amount / 100, 2);
    }

    public function formatNumber($number)
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return number_format($number / 1000, 1) . 'K';
        }
        
        return number_format($number);
    }

    public function getGamificationLevel($volume)
    {
        if ($volume >= 10000000) return ['level' => 'Legendary', 'color' => 'purple', 'icon' => 'crown'];
        if ($volume >= 5000000) return ['level' => 'Expert', 'color' => 'gold', 'icon' => 'star'];
        if ($volume >= 1000000) return ['level' => 'Advanced', 'color' => 'blue', 'icon' => 'trending-up'];
        if ($volume >= 500000) return ['level' => 'Intermediate', 'color' => 'green', 'icon' => 'zap'];
        return ['level' => 'Beginner', 'color' => 'gray', 'icon' => 'user'];
    }

    public function render()
    {
        return view('livewire.landing-page-component');
    }
}
