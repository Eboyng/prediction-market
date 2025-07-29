<?php

namespace App\Traits;

use App\Models\Referral;
use Illuminate\Support\Str;

/**
 * Referrable trait for managing referral logic
 * Used by User and Referral models
 */
trait Referrable
{
    /**
     * Get referrals made by this user (as referrer)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get referral received by this user (as referee)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function referralReceived()
    {
        return $this->hasOne(Referral::class, 'referee_id');
    }

    /**
     * Get the user's referral code
     *
     * @return string
     */
    public function getReferralCode(): string
    {
        if (empty($this->referral_code)) {
            $this->generateReferralCode();
        }
        
        return $this->referral_code;
    }

    /**
     * Generate a unique referral code for the user
     *
     * @return string
     */
    public function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        $this->update(['referral_code' => $code]);
        
        return $code;
    }

    /**
     * Create a referral relationship
     *
     * @param User $referee
     * @param int $bonusAmount
     * @return Referral|null
     */
    public function createReferral($referee, int $bonusAmount = 50000): ?Referral // Default 500 Naira bonus
    {
        // Check if referee already has a referral
        if ($referee->referralReceived()->exists()) {
            return null;
        }

        // Don't allow self-referral
        if ($this->id === $referee->id) {
            return null;
        }

        $referral = Referral::create([
            'referrer_id' => $this->id,
            'referee_id' => $referee->id,
            'bonus_amount' => $bonusAmount,
            'is_locked' => true,
            'lock_expires_at' => now()->addDays(30), // Lock for 30 days
            'status' => 'pending',
        ]);

        $this->logActivity('referral_created', [
            'referee_id' => $referee->id,
            'referee_name' => $referee->name,
            'bonus_amount' => $bonusAmount,
            'bonus_amount_naira' => $bonusAmount / 100,
        ]);

        return $referral;
    }

    /**
     * Get total referral earnings (locked + unlocked)
     *
     * @return int
     */
    public function getTotalReferralEarnings(): int
    {
        return $this->referralsMade()
            ->where('status', '!=', 'cancelled')
            ->sum('bonus_amount');
    }

    /**
     * Get unlocked referral earnings
     *
     * @return int
     */
    public function getUnlockedReferralEarnings(): int
    {
        return $this->referralsMade()
            ->where('is_locked', false)
            ->where('status', 'completed')
            ->sum('bonus_amount');
    }

    /**
     * Get locked referral earnings
     *
     * @return int
     */
    public function getLockedReferralEarnings(): int
    {
        return $this->referralsMade()
            ->where('is_locked', true)
            ->where('status', '!=', 'cancelled')
            ->sum('bonus_amount');
    }

    /**
     * Unlock referral bonuses that have expired their lock period
     *
     * @return int Number of bonuses unlocked
     */
    public function unlockExpiredReferralBonuses(): int
    {
        $expiredReferrals = $this->referralsMade()
            ->where('is_locked', true)
            ->where('lock_expires_at', '<=', now())
            ->where('status', 'active')
            ->get();

        $unlockedCount = 0;

        foreach ($expiredReferrals as $referral) {
            $referral->update([
                'is_locked' => false,
                'status' => 'completed',
            ]);

            // Add bonus to wallet
            $this->addFunds($referral->bonus_amount, 'Referral bonus unlocked');

            $this->logActivity('referral_bonus_unlocked', [
                'referral_id' => $referral->id,
                'referee_id' => $referral->referee_id,
                'bonus_amount' => $referral->bonus_amount,
                'bonus_amount_naira' => $referral->bonus_amount / 100,
            ]);

            $unlockedCount++;
        }

        return $unlockedCount;
    }

    /**
     * Get referral statistics
     *
     * @return array
     */
    public function getReferralStats(): array
    {
        return [
            'total_referrals' => $this->referralsMade()->count(),
            'active_referrals' => $this->referralsMade()->where('status', 'active')->count(),
            'completed_referrals' => $this->referralsMade()->where('status', 'completed')->count(),
            'total_earnings' => $this->getTotalReferralEarnings(),
            'unlocked_earnings' => $this->getUnlockedReferralEarnings(),
            'locked_earnings' => $this->getLockedReferralEarnings(),
            'total_earnings_naira' => $this->getTotalReferralEarnings() / 100,
            'unlocked_earnings_naira' => $this->getUnlockedReferralEarnings() / 100,
            'locked_earnings_naira' => $this->getLockedReferralEarnings() / 100,
        ];
    }

    /**
     * Check if user was referred by someone
     *
     * @return bool
     */
    public function wasReferred(): bool
    {
        return $this->referralReceived()->exists();
    }

    /**
     * Get the user who referred this user
     *
     * @return User|null
     */
    public function getReferrer()
    {
        $referral = $this->referralReceived()->first();
        return $referral ? $referral->referrer : null;
    }

    /**
     * Activate a referral (when referee meets requirements)
     *
     * @param Referral $referral
     * @return bool
     */
    public function activateReferral(Referral $referral): bool
    {
        if ($referral->referrer_id !== $this->id) {
            return false;
        }

        $updated = $referral->update(['status' => 'active']);

        if ($updated) {
            $this->logActivity('referral_activated', [
                'referral_id' => $referral->id,
                'referee_id' => $referral->referee_id,
                'bonus_amount' => $referral->bonus_amount,
            ]);
        }

        return $updated;
    }
}
