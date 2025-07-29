<?php

namespace App\Livewire;

use App\Models\Market;
use App\Models\Stake;
use App\Services\OddsService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class MarketDetailComponent extends Component
{
    public Market $market;
    public $recentActivity = [];
    public $marketStats = [];
    public $userStakes = [];
    
    protected OddsService $oddsService;

    public function boot(OddsService $oddsService)
    {
        $this->oddsService = $oddsService;
    }

    public function mount(Market $market)
    {
        $this->market = $market->load(['category', 'stakes.user']);
        $this->loadMarketData();
    }

    public function loadMarketData()
    {
        // Load market statistics
        $this->marketStats = [
            'total_volume' => $this->market->stakes()->sum('amount'),
            'total_traders' => $this->market->stakes()->distinct('user_id')->count(),
            'yes_stakes' => $this->market->stakes()->where('side', 'yes')->sum('amount'),
            'no_stakes' => $this->market->stakes()->where('side', 'no')->sum('amount'),
            'total_stakes_count' => $this->market->stakes()->count(),
        ];

        // Calculate current odds
        try {
            $this->market->current_odds = [
                'yes' => $this->oddsService->calculateOdds($this->market, 'yes'),
                'no' => $this->oddsService->calculateOdds($this->market, 'no'),
            ];
        } catch (\Exception $e) {
            $this->market->current_odds = [
                'yes' => 0.5,
                'no' => 0.5,
            ];
        }

        // Load recent activity (last 10 stakes)
        $this->recentActivity = $this->market->stakes()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Load user's stakes for this market if authenticated
        if (Auth::check()) {
            $this->userStakes = $this->market->stakes()
                ->where('user_id', Auth::id())
                ->latest()
                ->get();
        }
    }

    public function refreshData()
    {
        $this->market->refresh();
        $this->loadMarketData();
    }

    public function getTimeRemaining()
    {
        $now = now();
        $closes = \Carbon\Carbon::parse($this->market->closes_at);
        
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

    #[Title('Market Details - PredictNaira')]
    public function title()
    {
        return $this->market->question . ' - PredictNaira';
    }

    public function render()
    {
        return view('livewire.market-detail-component');
    }
}
