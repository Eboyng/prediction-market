<?php

namespace App\Livewire;

use App\Models\Market;
use App\Models\Stake;
use App\Services\OddsService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    /**
     * Place a bet on the market
     */
    public function placeBet($side, $amount)
    {
        // Validate user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Validate inputs
        $this->validate([
            'side' => 'required|in:yes,no',
            'amount' => 'required|numeric|min:1|max:1000000',
        ], [
            'side.required' => 'Please select a side to bet on.',
            'side.in' => 'Invalid betting side selected.',
            'amount.required' => 'Please enter a bet amount.',
            'amount.numeric' => 'Bet amount must be a number.',
            'amount.min' => 'Minimum bet amount is ₦1.',
            'amount.max' => 'Maximum bet amount is ₦1,000,000.',
        ]);

        try {
            DB::transaction(function () use ($side, $amount) {
                $user = Auth::user();
                $amountInKobo = (int) ($amount * 100);
                
                // Validate user balance
                if (!$user->hasSufficientBalance($amountInKobo)) {
                    throw ValidationException::withMessages([
                        'amount' => 'Insufficient wallet balance. Please deposit funds.'
                    ]);
                }
                
                // Validate market is still open
                if (!$this->market->isOpen()) {
                    throw ValidationException::withMessages([
                        'market' => 'This market is no longer accepting bets.'
                    ]);
                }
                
                // Calculate current odds
                $currentOdds = $this->oddsService->calculateOdds($this->market, $side);
                
                // Deduct funds from wallet
                $user->deductFunds($amountInKobo, "Bet on: {$this->market->question}");
                
                // Create stake record
                $stake = Stake::create([
                    'user_id' => $user->id,
                    'market_id' => $this->market->id,
                    'side' => $side,
                    'amount' => $amountInKobo,
                    'odds_at_placement' => $currentOdds,
                ]);
                
                // Log activity
                $user->logActivity('stake_placed', [
                    'stake_id' => $stake->id,
                    'market_id' => $this->market->id,
                    'market_question' => $this->market->question,
                    'side' => $side,
                    'amount' => $amountInKobo,
                    'amount_naira' => $amount,
                    'odds' => $currentOdds,
                ]);
            });
            
            // Refresh market data
            $this->loadMarketData();
            
            // Emit events
            $this->dispatch('stake-placed', [
                'marketId' => $this->market->id,
                'userId' => Auth::id(),
                'amount' => $amount,
                'side' => $side,
            ]);
            
            $this->dispatch('wallet-updated', ['userId' => Auth::id()]);
            $this->dispatch('market-updated', ['marketId' => $this->market->id]);
            
            // Show success message
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Bet placed successfully!'
            ]);
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Failed to place bet. Please try again.'
            ]);
        }
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
