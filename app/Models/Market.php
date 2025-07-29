<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Market extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question',
        'description',
        'category_id',
        'closes_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'closes_at' => 'datetime',
        ];
    }

    /**
     * Get the category that owns the market.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stakes for the market.
     */
    public function stakes()
    {
        return $this->hasMany(Stake::class);
    }

    /**
     * Check if market is open
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->status === 'open' && $this->closes_at > now();
    }

    /**
     * Check if market is closed
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->status !== 'open' || $this->closes_at <= now();
    }

    /**
     * Get total stake amount for the market
     *
     * @return int
     */
    public function getTotalStakeAmount(): int
    {
        return $this->stakes()->sum('amount');
    }
}
