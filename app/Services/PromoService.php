<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PromoService handles promo code management, validation, and campaign operations
 */
class PromoService
{
    /**
     * Create a new promo code
     */
    public function createPromoCode(array $data): PromoCode
    {
        $data['code'] = strtoupper($data['code']);
        
        return PromoCode::create([
            'code' => $data['code'],
            'discount_percent' => $data['discount_percent'],
            'expires_at' => $data['expires_at'],
            'usage_limit' => $data['usage_limit'] ?? null,
            'used_count' => 0,
            'is_active' => $data['is_active'] ?? true,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Generate bulk promo codes for campaigns
     */
    public function generateBulkPromoCodes(
        string $prefix,
        int $count,
        float $discountPercent,
        Carbon $expiresAt,
        ?int $usageLimit = null,
        ?string $description = null
    ): array {
        $promoCodes = [];
        
        DB::transaction(function () use (
            $prefix, $count, $discountPercent, $expiresAt, 
            $usageLimit, $description, &$promoCodes
        ) {
            for ($i = 1; $i <= $count; $i++) {
                $code = strtoupper($prefix . '_' . Str::random(6));
                
                // Ensure uniqueness
                while (PromoCode::where('code', $code)->exists()) {
                    $code = strtoupper($prefix . '_' . Str::random(6));
                }
                
                $promoCode = PromoCode::create([
                    'code' => $code,
                    'discount_percent' => $discountPercent,
                    'expires_at' => $expiresAt,
                    'usage_limit' => $usageLimit,
                    'used_count' => 0,
                    'is_active' => true,
                    'description' => $description,
                ]);
                
                $promoCodes[] = $promoCode;
            }
        });
        
        return $promoCodes;
    }

    /**
     * Validate a promo code for a specific user
     */
    public function validatePromoCode(string $code, User $user): array
    {
        $promoCode = PromoCode::where('code', strtoupper($code))->first();
        
        if (!$promoCode) {
            return [
                'valid' => false,
                'error' => 'Promo code not found',
                'code' => 'PROMO_NOT_FOUND'
            ];
        }
        
        if (!$promoCode->is_active) {
            return [
                'valid' => false,
                'error' => 'Promo code is inactive',
                'code' => 'PROMO_INACTIVE'
            ];
        }
        
        if ($promoCode->expires_at->isPast()) {
            return [
                'valid' => false,
                'error' => 'Promo code has expired',
                'code' => 'PROMO_EXPIRED'
            ];
        }
        
        if ($promoCode->usage_limit && $promoCode->used_count >= $promoCode->usage_limit) {
            return [
                'valid' => false,
                'error' => 'Promo code usage limit reached',
                'code' => 'PROMO_EXHAUSTED'
            ];
        }
        
        // Check if user has already used this promo code
        $hasUsed = ActivityLog::where('user_id', $user->id)
            ->where('action', 'promo_redeemed')
            ->whereJsonContains('metadata->promo_code', $promoCode->code)
            ->exists();
            
        if ($hasUsed) {
            return [
                'valid' => false,
                'error' => 'You have already used this promo code',
                'code' => 'PROMO_ALREADY_USED'
            ];
        }
        
        return [
            'valid' => true,
            'promo_code' => $promoCode,
            'discount_percent' => $promoCode->discount_percent
        ];
    }

    /**
     * Apply a promo code to a stake amount
     */
    public function applyPromoCode(string $code, int $stakeAmount, User $user): array
    {
        $validation = $this->validatePromoCode($code, $user);
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        $promoCode = $validation['promo_code'];
        $discountAmount = (int) ($stakeAmount * ($promoCode->discount_percent / 100));
        $finalAmount = $stakeAmount - $discountAmount;
        
        return [
            'valid' => true,
            'promo_code' => $promoCode,
            'original_amount' => $stakeAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'discount_percent' => $promoCode->discount_percent,
            'savings' => $discountAmount
        ];
    }

    /**
     * Redeem a promo code (mark as used)
     */
    public function redeemPromoCode(PromoCode $promoCode, User $user, int $discountAmount): void
    {
        DB::transaction(function () use ($promoCode, $user, $discountAmount) {
            // Increment usage count
            $promoCode->increment('used_count');
            
            // Log the redemption
            $user->logActivity('promo_redeemed', [
                'promo_code' => $promoCode->code,
                'discount_percent' => $promoCode->discount_percent,
                'discount_amount' => $discountAmount,
                'promo_id' => $promoCode->id,
            ]);
        });
    }

    /**
     * Get promo code usage statistics
     */
    public function getPromoCodeStats(PromoCode $promoCode): array
    {
        $totalRedemptions = ActivityLog::where('action', 'promo_redeemed')
            ->whereJsonContains('metadata->promo_code', $promoCode->code)
            ->count();
            
        $totalSavings = ActivityLog::where('action', 'promo_redeemed')
            ->whereJsonContains('metadata->promo_code', $promoCode->code)
            ->sum('metadata->discount_amount');
            
        $usageRate = $promoCode->usage_limit 
            ? ($promoCode->used_count / $promoCode->usage_limit) * 100 
            : 0;
            
        $daysUntilExpiry = $promoCode->expires_at->diffInDays(now(), false);
        
        return [
            'code' => $promoCode->code,
            'total_redemptions' => $totalRedemptions,
            'total_savings' => $totalSavings,
            'usage_rate' => round($usageRate, 2),
            'days_until_expiry' => $daysUntilExpiry,
            'is_expired' => $promoCode->expires_at->isPast(),
            'is_exhausted' => $promoCode->usage_limit && $promoCode->used_count >= $promoCode->usage_limit,
            'remaining_uses' => $promoCode->usage_limit ? max(0, $promoCode->usage_limit - $promoCode->used_count) : null,
        ];
    }

    /**
     * Get campaign performance analytics
     */
    public function getCampaignAnalytics(array $promoCodes = null): array
    {
        $query = ActivityLog::where('action', 'promo_redeemed');
        
        if ($promoCodes) {
            $query->whereIn('metadata->promo_code', $promoCodes);
        }
        
        $redemptions = $query->get();
        
        $totalRedemptions = $redemptions->count();
        $totalSavings = $redemptions->sum('metadata.discount_amount');
        $uniqueUsers = $redemptions->unique('user_id')->count();
        
        $topCodes = $redemptions->groupBy('metadata.promo_code')
            ->map(function ($group, $code) {
                return [
                    'code' => $code,
                    'redemptions' => $group->count(),
                    'savings' => $group->sum('metadata.discount_amount'),
                    'unique_users' => $group->unique('user_id')->count(),
                ];
            })
            ->sortByDesc('redemptions')
            ->take(10)
            ->values();
            
        $dailyRedemptions = $redemptions->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map(function ($group) {
                return [
                    'redemptions' => $group->count(),
                    'savings' => $group->sum('metadata.discount_amount'),
                ];
            });
        
        return [
            'total_redemptions' => $totalRedemptions,
            'total_savings' => $totalSavings,
            'unique_users' => $uniqueUsers,
            'average_savings_per_redemption' => $totalRedemptions > 0 ? $totalSavings / $totalRedemptions : 0,
            'top_performing_codes' => $topCodes,
            'daily_redemptions' => $dailyRedemptions,
        ];
    }

    /**
     * Deactivate expired promo codes
     */
    public function deactivateExpiredPromoCodes(): int
    {
        return PromoCode::where('is_active', true)
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);
    }

    /**
     * Get active promo codes for a category or general use
     */
    public function getActivePromoCodes(?string $category = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = PromoCode::where('is_active', true)
            ->where('expires_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            });
            
        if ($category) {
            $query->where('description', 'like', "%{$category}%");
        }
        
        return $query->orderBy('discount_percent', 'desc')->get();
    }

    /**
     * Create a referral promo code for a user
     */
    public function createReferralPromoCode(User $user, float $discountPercent = 10.0): PromoCode
    {
        $code = 'REF_' . strtoupper($user->referral_code);
        
        // Check if referral promo already exists
        $existingPromo = PromoCode::where('code', $code)->first();
        
        if ($existingPromo) {
            return $existingPromo;
        }
        
        return $this->createPromoCode([
            'code' => $code,
            'discount_percent' => $discountPercent,
            'expires_at' => now()->addYear(),
            'usage_limit' => 100, // Limit referral promos
            'description' => "Referral promo code for {$user->name}",
        ]);
    }

    /**
     * Calculate fee discount for a transaction
     */
    public function calculateFeeDiscount(int $feeAmount, float $discountPercent): array
    {
        $discountAmount = (int) ($feeAmount * ($discountPercent / 100));
        $finalFee = $feeAmount - $discountAmount;
        
        return [
            'original_fee' => $feeAmount,
            'discount_amount' => $discountAmount,
            'final_fee' => $finalFee,
            'discount_percent' => $discountPercent,
        ];
    }
}
