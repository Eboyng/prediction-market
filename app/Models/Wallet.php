<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Wallet extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'balance',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'balance' => 'integer',
        ];
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
        return $this->balance >= $amountInKobo;
    }

    /**
     * Get balance in Naira (formatted)
     *
     * @return float
     */
    public function getBalanceInNaira(): float
    {
        return $this->balance / 100;
    }

    /**
     * Get formatted balance
     *
     * @return string
     */
    public function getFormattedBalance(): string
    {
        return '₦' . number_format($this->getBalanceInNaira(), 2);
    }
}
