<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class WalletBalanceComponent extends Component
{
    public $balance = 0;
    public $formattedBalance = '₦0.00';
    public $isLoading = false;

    /**
     * Mount the component
     */
    public function mount()
    {
        $this->refreshBalance();
    }

    /**
     * Refresh wallet balance
     */
    public function refreshBalance()
    {
        if (!Auth::check()) {
            $this->balance = 0;
            $this->formattedBalance = '₦0.00';
            return;
        }

        $user = Auth::user();
        $this->balance = $user->getWalletBalance();
        $this->formattedBalance = $user->getFormattedWalletBalance();
    }

    /**
     * Listen for wallet balance updates
     */
    #[On('wallet-updated')]
    public function handleWalletUpdate($userId = null)
    {
        // Only update if it's the current user or no specific user
        if (!$userId || (Auth::check() && Auth::id() === $userId)) {
            $this->refreshBalance();
        }
    }

    /**
     * Listen for stake placed events
     */
    #[On('stake-placed')]
    public function handleStakePlaced()
    {
        $this->refreshBalance();
    }

    /**
     * Listen for deposit events
     */
    #[On('deposit-received')]
    public function handleDepositReceived()
    {
        $this->refreshBalance();
    }

    /**
     * Listen for withdrawal events
     */
    #[On('withdrawal-processed')]
    public function handleWithdrawalProcessed()
    {
        $this->refreshBalance();
    }

    /**
     * Manual refresh with loading state
     */
    public function manualRefresh()
    {
        $this->isLoading = true;
        
        // Simulate a small delay for better UX
        usleep(500000); // 0.5 seconds
        
        $this->refreshBalance();
        $this->isLoading = false;
        
        // Show success message
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Balance refreshed successfully!'
        ]);
    }

    /**
     * Get balance in Naira
     */
    public function getBalanceInNaira()
    {
        return $this->balance / 100;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalance()
    {
        return $this->balance / 100;
    }

    /**
     * Get total winnings
     */
    public function getTotalWinnings()
    {
        if (!Auth::check()) {
            return 0;
        }
        
        $user = Auth::user();
        return $user->stakes()
            ->where('status', 'won')
            ->sum('payout_amount') / 100;
    }

    /**
     * Get total stakes
     */
    public function getTotalStakes()
    {
        if (!Auth::check()) {
            return 0;
        }
        
        $user = Auth::user();
        return $user->stakes()->sum('amount') / 100;
    }

    /**
     * Check if user has sufficient balance for amount
     */
    public function hasSufficientBalance($amountInKobo)
    {
        return $this->balance >= $amountInKobo;
    }

    /**
     * Navigate to deposit page
     */
    public function goToDeposit()
    {
        return redirect()->route('wallet.index');
    }

    /**
     * Navigate to withdrawal page
     */
    public function goToWithdraw()
    {
        return redirect()->route('wallet.index');
    }

    public function render()
    {
        return view('livewire.wallet-balance-component');
    }
}
