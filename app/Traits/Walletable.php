<?php

namespace App\Traits;

use App\Models\Wallet;

/**
 * Walletable trait for wallet operations
 * Used by User and Wallet models
 */
trait Walletable
{
    /**
     * Get the user's wallet
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get or create wallet for the user
     *
     * @return Wallet
     */
    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet()->firstOrCreate([
            'user_id' => $this->id,
        ], [
            'balance' => 0,
        ]);
    }

    /**
     * Get wallet balance in kobo
     *
     * @return int
     */
    public function getWalletBalance(): int
    {
        return $this->getOrCreateWallet()->balance;
    }

    /**
     * Get wallet balance in Naira (formatted)
     *
     * @return float
     */
    public function getWalletBalanceInNaira(): float
    {
        return $this->getWalletBalance() / 100;
    }

    /**
     * Get formatted wallet balance
     *
     * @return string
     */
    public function getFormattedWalletBalance(): string
    {
        return '₦' . number_format($this->getWalletBalanceInNaira(), 2);
    }

    /**
     * Check if user has deposited a minimum amount
     *
     * @param int $amountInKobo
     * @return bool
     */
    public function hasDeposited(int $amountInKobo): bool
    {
        // This should check transaction history for deposits
        // For now, we'll check if wallet balance is >= amount
        return $this->getWalletBalance() >= $amountInKobo;
    }

    /**
     * Add funds to wallet
     *
     * @param int $amountInKobo
     * @param string $description
     * @return bool
     */
    public function addFunds(int $amountInKobo, string $description = 'Deposit'): bool
    {
        $wallet = $this->getOrCreateWallet();
        $updated = $wallet->increment('balance', $amountInKobo);

        if ($updated) {
            $this->logActivity('wallet_credit', [
                'amount' => $amountInKobo,
                'amount_naira' => $amountInKobo / 100,
                'description' => $description,
                'new_balance' => $wallet->fresh()->balance,
            ]);
        }

        return $updated;
    }

    /**
     * Deduct funds from wallet
     *
     * @param int $amountInKobo
     * @param string $description
     * @return bool
     */
    public function deductFunds(int $amountInKobo, string $description = 'Withdrawal'): bool
    {
        $wallet = $this->getOrCreateWallet();
        
        if ($wallet->balance < $amountInKobo) {
            return false;
        }

        $updated = $wallet->decrement('balance', $amountInKobo);

        if ($updated) {
            $this->logActivity('wallet_debit', [
                'amount' => $amountInKobo,
                'amount_naira' => $amountInKobo / 100,
                'description' => $description,
                'new_balance' => $wallet->fresh()->balance,
            ]);
        }

        return $updated;
    }

    /**
     * Check if user has sufficient balance
     *
     * @param int $amountInKobo
     * @return bool
     */
    public function hasSufficientBalance(int $amountInKobo): bool
    {
        return $this->getWalletBalance() >= $amountInKobo;
    }

    /**
     * Validate withdrawal rules
     *
     * @param int $amountInKobo
     * @throws \Illuminate\Validation\ValidationException
     * @return void
     */
    public function validateWithdrawal(int $amountInKobo): void
    {
        // Check KYC status
        $this->validateKycForWithdrawal();

        // Check minimum deposit requirement for first-time withdrawal
        if (!$this->hasDeposited(500000) && $amountInKobo > 0) { // 5000 Naira in kobo
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => 'First-time withdrawal requires a ₦5,000 deposit.',
            ]);
        }

        // Check minimum withdrawal amount for returning users
        if ($this->hasDeposited(500000) && $amountInKobo < 100000) { // 1000 Naira in kobo
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => 'Minimum withdrawal for returning users is ₦1,000.',
            ]);
        }

        // Check sufficient balance
        if (!$this->hasSufficientBalance($amountInKobo)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => 'Insufficient wallet balance.',
            ]);
        }
    }

    /**
     * Transfer funds to another user
     *
     * @param User $recipient
     * @param int $amountInKobo
     * @param string $description
     * @return bool
     */
    public function transferFunds($recipient, int $amountInKobo, string $description = 'Transfer'): bool
    {
        if (!$this->hasSufficientBalance($amountInKobo)) {
            return false;
        }

        $deducted = $this->deductFunds($amountInKobo, "Transfer to {$recipient->name}");
        
        if ($deducted) {
            $recipient->addFunds($amountInKobo, "Transfer from {$this->name}");
            
            $this->logActivity('funds_transferred', [
                'recipient_id' => $recipient->id,
                'recipient_name' => $recipient->name,
                'amount' => $amountInKobo,
                'amount_naira' => $amountInKobo / 100,
                'description' => $description,
            ]);
        }

        return $deducted;
    }
}
