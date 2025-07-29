<?php

namespace App\Livewire;

use App\Models\Market;
use App\Models\Stake;
use App\Models\PromoCode;
use App\Services\OddsService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StakeFormComponent extends Component
{
    public Market $market;
    public $side = 'yes';
    public $amount = '';
    public $promoCode = '';
    public $appliedPromo = null;
    public $isSubmitting = false;
    public $showConfirmation = false;
    
    // Calculated values
    public $currentOdds = 0;
    public $potentialPayout = 0;
    public $potentialProfit = 0;
    public $marketImpact = [];
    
    protected $rules = [
        'side' => 'required|in:yes,no',
        'amount' => 'required|numeric|min:100|max:1000000', // Min 1 Naira, Max 10k Naira
        'promoCode' => 'nullable|string|max:20',
    ];
    
    protected $messages = [
        'amount.min' => 'Minimum stake amount is ₦1.00',
        'amount.max' => 'Maximum stake amount is ₦10,000.00',
        'amount.required' => 'Please enter a stake amount',
        'amount.numeric' => 'Stake amount must be a valid number',
    ];

    public function mount(Market $market)
    {
        $this->market = $market;
        $this->updateCalculations();
    }

    /**
     * Update calculations when amount or side changes
     */
    public function updatedAmount()
    {
        $this->updateCalculations();
    }

    public function updatedSide()
    {
        $this->updateCalculations();
    }

    /**
     * Apply promo code
     */
    public function applyPromoCode()
    {
        if (empty($this->promoCode)) {
            $this->appliedPromo = null;
            $this->updateCalculations();
            return;
        }

        $promoCode = PromoCode::where('code', strtoupper($this->promoCode))
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->where('used_count', '<', DB::raw('usage_limit'))
            ->first();

        if (!$promoCode) {
            $this->addError('promoCode', 'Invalid or expired promo code');
            $this->appliedPromo = null;
        } else {
            $this->appliedPromo = $promoCode;
            $this->resetErrorBag('promoCode');
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Promo code applied! {$promoCode->discount_percent}% bonus on winnings."
            ]);
        }

        $this->updateCalculations();
    }

    /**
     * Remove applied promo code
     */
    public function removePromoCode()
    {
        $this->promoCode = '';
        $this->appliedPromo = null;
        $this->updateCalculations();
    }

    /**
     * Update all calculations
     */
    private function updateCalculations()
    {
        if (empty($this->amount) || !is_numeric($this->amount)) {
            $this->resetCalculations();
            return;
        }

        $amountInKobo = (int) ($this->amount * 100);
        $oddsService = app(OddsService::class);

        // Calculate odds with promo
        $oddsData = $oddsService->calculateOddsWithPromo(
            $this->market,
            $this->side,
            $this->appliedPromo,
            $amountInKobo
        );

        $this->currentOdds = $oddsData['final_odds'];

        // Calculate payout
        $payoutData = $oddsService->calculatePayout(
            $amountInKobo,
            $this->currentOdds,
            $this->appliedPromo
        );

        $this->potentialPayout = $payoutData['final_payout_naira'];
        $this->potentialProfit = $payoutData['potential_profit_naira'];

        // Calculate market impact
        $this->marketImpact = $oddsService->calculateMarketImpact(
            $this->market,
            $this->side,
            $amountInKobo
        );
    }

    /**
     * Reset calculations
     */
    private function resetCalculations()
    {
        $this->currentOdds = 0;
        $this->potentialPayout = 0;
        $this->potentialProfit = 0;
        $this->marketImpact = [];
    }

    /**
     * Show confirmation dialog
     */
    public function showConfirmation()
    {
        $this->validate();
        
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Validate user balance
        $user = Auth::user();
        $amountInKobo = (int) ($this->amount * 100);
        
        if (!$user->hasSufficientBalance($amountInKobo)) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient wallet balance. Please deposit funds.'
            ]);
        }

        // Validate market is still open
        if (!$this->market->isOpen()) {
            throw ValidationException::withMessages([
                'market' => 'This market is no longer accepting stakes.'
            ]);
        }

        $this->showConfirmation = true;
    }

    /**
     * Place the stake
     */
    public function placeStake()
    {
        $this->isSubmitting = true;
        
        try {
            DB::transaction(function () {
                $user = Auth::user();
                $amountInKobo = (int) ($this->amount * 100);
                
                // Final validation
                if (!$user->hasSufficientBalance($amountInKobo)) {
                    throw ValidationException::withMessages([
                        'amount' => 'Insufficient wallet balance.'
                    ]);
                }
                
                if (!$this->market->isOpen()) {
                    throw ValidationException::withMessages([
                        'market' => 'Market is no longer open.'
                    ]);
                }
                
                // Deduct funds from wallet
                $user->deductFunds($amountInKobo, "Stake on: {$this->market->question}");
                
                // Create stake record
                $stake = Stake::create([
                    'user_id' => $user->id,
                    'market_id' => $this->market->id,
                    'side' => $this->side,
                    'amount' => $amountInKobo,
                    'odds_at_placement' => $this->currentOdds,
                ]);
                
                // Use promo code if applied
                if ($this->appliedPromo) {
                    $oddsService = app(OddsService::class);
                    $oddsService->usePromoCode($this->appliedPromo);
                    
                    // Log promo usage
                    $user->logActivity('promo_code_used', [
                        'promo_code' => $this->appliedPromo->code,
                        'discount_percent' => $this->appliedPromo->discount_percent,
                        'stake_id' => $stake->id,
                        'market_id' => $this->market->id,
                    ]);
                }
                
                // Log stake activity
                $user->logActivity('stake_placed', [
                    'stake_id' => $stake->id,
                    'market_id' => $this->market->id,
                    'market_question' => $this->market->question,
                    'side' => $this->side,
                    'amount' => $amountInKobo,
                    'amount_naira' => $this->amount,
                    'odds' => $this->currentOdds,
                    'potential_payout' => $this->potentialPayout,
                ]);
            });
            
            // Emit events
            $this->dispatch('stake-placed', [
                'marketId' => $this->market->id,
                'userId' => Auth::id(),
                'amount' => $this->amount,
                'side' => $this->side,
            ]);
            
            $this->dispatch('wallet-updated', ['userId' => Auth::id()]);
            $this->dispatch('market-updated', ['marketId' => $this->market->id]);
            
            // Show success message
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Stake placed successfully! ₦{$this->amount} on {$this->side}."
            ]);
            
            // Reset form
            $this->reset(['amount', 'promoCode', 'appliedPromo', 'showConfirmation']);
            $this->updateCalculations();
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'An error occurred while placing your stake. Please try again.'
            ]);
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Cancel confirmation
     */
    public function cancelConfirmation()
    {
        $this->showConfirmation = false;
    }

    /**
     * Listen for market updates
     */
    #[On('market-updated')]
    public function handleMarketUpdate($marketId)
    {
        if ($marketId === $this->market->id) {
            $this->market->refresh();
            $this->updateCalculations();
        }
    }

    public function render()
    {
        return view('livewire.stake-form-component');
    }
}
