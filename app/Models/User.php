<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;
use App\Traits\NotifiableTrait;
use App\Traits\KycVerifiable;
use App\Traits\Walletable;
use App\Traits\Referrable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Auditable, NotifiableTrait, KycVerifiable, Walletable, Referrable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_verified',
        'kyc_status',
        'dark_mode',
        'referral_code',
        'email_notifications',
        'sms_notifications',
        'in_app_notifications',
        'market_updates',
        'stake_confirmations',
        'withdrawal_updates',
        'referral_updates',
        'promo_notifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'dark_mode' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'in_app_notifications' => 'boolean',
            'market_updates' => 'boolean',
            'stake_confirmations' => 'boolean',
            'withdrawal_updates' => 'boolean',
            'referral_updates' => 'boolean',
            'promo_notifications' => 'boolean',
        ];
    }

    /**
     * Get the user's wallet balance in kobo
     */
    public function getWalletBalance(): int
    {
        return $this->wallet ? $this->wallet->balance : 0;
    }

    /**
     * Get the user's formatted wallet balance
     */
    public function getFormattedWalletBalance(): string
    {
        $balance = $this->getWalletBalance();
        return '₦' . number_format($balance / 100, 2);
    }

    /**
     * Get the user's wallet balance in naira (float)
     */
    public function getWalletBalanceInNaira(): float
    {
        return $this->getWalletBalance() / 100;
    }

    /**
     * Check if user has sufficient balance for amount in kobo
     */
    public function hasSufficientBalance(int $amountInKobo): bool
    {
        return $this->getWalletBalance() >= $amountInKobo;
    }

    /**
     * Get user's total stakes amount
     */
    public function getTotalStakesAmount(): int
    {
        return $this->stakes()->sum('amount') ?? 0;
    }

    /**
     * Get user's total winnings
     */
    public function getTotalWinnings(): int
    {
        return $this->stakes()->where('status', 'won')->sum('payout_amount') ?? 0;
    }

    /**
     * Get user's active stakes count
     */
    public function getActiveStakesCount(): int
    {
        return $this->stakes()->where('status', 'active')->count();
    }

    /**
     * Get user's win rate percentage
     */
    public function getWinRate(): float
    {
        $totalCompletedStakes = $this->stakes()->whereIn('status', ['won', 'lost'])->count();
        if ($totalCompletedStakes === 0) {
            return 0;
        }
        
        $wonStakes = $this->stakes()->where('status', 'won')->count();
        return round(($wonStakes / $totalCompletedStakes) * 100, 2);
    }
}
