<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Stake extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'market_id',
        'side',
        'amount',
        'odds_at_placement',
        'status',
        'payout_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'odds_at_placement' => 'decimal:4',
            'amount' => 'integer',
        ];
    }

    /**
     * Get the user that owns the stake.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the market that owns the stake.
     */
    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Check if stake is on 'yes' side
     *
     * @return bool
     */
    public function isYes(): bool
    {
        return $this->side === 'yes';
    }

    /**
     * Check if stake is on 'no' side
     *
     * @return bool
     */
    public function isNo(): bool
    {
        return $this->side === 'no';
    }

    /**
     * Get formatted amount in Naira
     *
     * @return string
     */
    public function getFormattedAmount(): string
    {
        return '₦' . number_format($this->amount / 100, 2);
    }

    /**
     * Get formatted payout amount in Naira
     *
     * @return string
     */
    public function getFormattedPayout(): string
    {
        return '₦' . number_format($this->payout_amount / 100, 2);
    }

    /**
     * Check if stake is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if stake won
     *
     * @return bool
     */
    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    /**
     * Check if stake lost
     *
     * @return bool
     */
    public function isLost(): bool
    {
        return $this->status === 'lost';
    }

    /**
     * Check if stake is cancelled
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
