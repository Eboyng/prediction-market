<?php

namespace App\Services;

use App\Models\Market;
use App\Models\Stake;
use App\Models\PromoCode;

/**
 * OddsService for calculating market odds using AMM formula
 * Factors in promo discounts and market liquidity
 */
class OddsService
{
    /**
     * Calculate odds for a market side using improved AMM formula
     *
     * @param Market $market
     * @param string $side ('yes' or 'no')
     * @param int|null $additionalStake Additional stake amount to simulate
     * @return float
     */
    public function calculateOdds(Market $market, string $side, ?int $additionalStake = null): float
    {
        $totalYesStakes = $market->stakes()->where('side', 'yes')->sum('amount');
        $totalNoStakes = $market->stakes()->where('side', 'no')->sum('amount');

        // Add additional stake for simulation
        if ($additionalStake) {
            if ($side === 'yes') {
                $totalYesStakes += $additionalStake;
            } else {
                $totalNoStakes += $additionalStake;
            }
        }

        // Base liquidity pool (50,000 Naira in kobo split equally)
        $baseLiquidity = 5000000; // 50,000 Naira in kobo
        $yesPool = $totalYesStakes + ($baseLiquidity / 2);
        $noPool = $totalNoStakes + ($baseLiquidity / 2);
        
        // Ensure minimum liquidity to prevent division by zero
        $yesPool = max($yesPool, 100000); // Minimum 1,000 Naira
        $noPool = max($noPool, 100000);   // Minimum 1,000 Naira
        
        $totalPool = $yesPool + $noPool;

        // Calculate probability based on pool ratios
        if ($side === 'yes') {
            $probability = $noPool / $totalPool; // Inverse relationship for odds
        } else {
            $probability = $yesPool / $totalPool; // Inverse relationship for odds
        }

        // Ensure probability bounds (5% to 95%)
        $probability = max(0.05, min(0.95, $probability));
        
        // Calculate fair odds
        $fairOdds = 1 / $probability;
        
        // Apply house edge (3% reduction in odds)
        $odds = $fairOdds * 0.97;
        
        // Ensure minimum odds of 1.05 and maximum of 19.0
        $odds = max(1.05, min(19.0, $odds));

        return round($odds, 2);
    }

    /**
     * Calculate odds with promo discount applied
     *
     * @param Market $market
     * @param string $side
     * @param PromoCode|null $promoCode
     * @param int|null $additionalStake
     * @return array
     */
    public function calculateOddsWithPromo(Market $market, string $side, ?PromoCode $promoCode = null, ?int $additionalStake = null): array
    {
        $baseOdds = $this->calculateOdds($market, $side, $additionalStake);
        $discountPercent = 0;
        $finalOdds = $baseOdds;

        if ($promoCode && $this->isPromoCodeValid($promoCode)) {
            $discountPercent = $promoCode->discount_percent;
            // Apply discount to improve odds for the user
            $finalOdds = $baseOdds * (1 + ($discountPercent / 100));
        }

        return [
            'base_odds' => $baseOdds,
            'final_odds' => round($finalOdds, 2),
            'discount_percent' => $discountPercent,
            'discount_applied' => $discountPercent > 0,
        ];
    }

    /**
     * Calculate potential payout for a stake
     *
     * @param int $stakeAmount Amount in kobo
     * @param float $odds
     * @param PromoCode|null $promoCode
     * @return array
     */
    public function calculatePayout(int $stakeAmount, float $odds, ?PromoCode $promoCode = null): array
    {
        $basePayout = $stakeAmount * $odds;
        $discountPercent = 0;
        $finalPayout = $basePayout;

        if ($promoCode && $this->isPromoCodeValid($promoCode)) {
            $discountPercent = $promoCode->discount_percent;
            $finalPayout = $basePayout * (1 + ($discountPercent / 100));
        }

        return [
            'stake_amount' => $stakeAmount,
            'stake_amount_naira' => $stakeAmount / 100,
            'base_payout' => round($basePayout),
            'base_payout_naira' => round($basePayout / 100, 2),
            'final_payout' => round($finalPayout),
            'final_payout_naira' => round($finalPayout / 100, 2),
            'potential_profit' => round($finalPayout - $stakeAmount),
            'potential_profit_naira' => round(($finalPayout - $stakeAmount) / 100, 2),
            'discount_percent' => $discountPercent,
            'discount_applied' => $discountPercent > 0,
        ];
    }

    /**
     * Get market liquidity information
     *
     * @param Market $market
     * @return array
     */
    public function getMarketLiquidity(Market $market): array
    {
        $totalYesStakes = $market->stakes()->where('side', 'yes')->sum('amount');
        $totalNoStakes = $market->stakes()->where('side', 'no')->sum('amount');
        $totalStakes = $totalYesStakes + $totalNoStakes;

        $yesPercentage = $totalStakes > 0 ? ($totalYesStakes / $totalStakes) * 100 : 50;
        $noPercentage = $totalStakes > 0 ? ($totalNoStakes / $totalStakes) * 100 : 50;

        return [
            'total_stakes' => $totalStakes,
            'total_stakes_naira' => $totalStakes / 100,
            'yes_stakes' => $totalYesStakes,
            'yes_stakes_naira' => $totalYesStakes / 100,
            'no_stakes' => $totalNoStakes,
            'no_stakes_naira' => $totalNoStakes / 100,
            'yes_percentage' => round($yesPercentage, 1),
            'no_percentage' => round($noPercentage, 1),
            'liquidity_level' => $this->getLiquidityLevel($totalStakes),
        ];
    }

    /**
     * Validate promo code
     *
     * @param PromoCode $promoCode
     * @return bool
     */
    public function isPromoCodeValid(PromoCode $promoCode): bool
    {
        return $promoCode->is_active
            && $promoCode->expires_at > now()
            && $promoCode->used_count < $promoCode->usage_limit;
    }

    /**
     * Apply promo code usage
     *
     * @param PromoCode $promoCode
     * @return bool
     */
    public function usePromoCode(PromoCode $promoCode): bool
    {
        if (!$this->isPromoCodeValid($promoCode)) {
            return false;
        }

        $promoCode->increment('used_count');
        return true;
    }

    /**
     * Get liquidity level description
     *
     * @param int $totalStakes
     * @return string
     */
    private function getLiquidityLevel(int $totalStakes): string
    {
        $totalNaira = $totalStakes / 100;

        if ($totalNaira >= 100000) { // 100k Naira
            return 'High';
        } elseif ($totalNaira >= 10000) { // 10k Naira
            return 'Medium';
        } elseif ($totalNaira >= 1000) { // 1k Naira
            return 'Low';
        } else {
            return 'Very Low';
        }
    }

    /**
     * Calculate market impact of a potential stake
     *
     * @param Market $market
     * @param string $side
     * @param int $stakeAmount
     * @return array
     */
    public function calculateMarketImpact(Market $market, string $side, int $stakeAmount): array
    {
        $currentOdds = $this->calculateOdds($market, $side);
        $newOdds = $this->calculateOdds($market, $side, $stakeAmount);
        
        $oddsChange = $newOdds - $currentOdds;
        $oddsChangePercent = $currentOdds > 0 ? ($oddsChange / $currentOdds) * 100 : 0;

        return [
            'current_odds' => $currentOdds,
            'new_odds' => $newOdds,
            'odds_change' => round($oddsChange, 2),
            'odds_change_percent' => round($oddsChangePercent, 2),
            'impact_level' => $this->getImpactLevel(abs($oddsChangePercent)),
        ];
    }

    /**
     * Get impact level description
     *
     * @param float $changePercent
     * @return string
     */
    private function getImpactLevel(float $changePercent): string
    {
        if ($changePercent >= 10) {
            return 'High';
        } elseif ($changePercent >= 5) {
            return 'Medium';
        } elseif ($changePercent >= 1) {
            return 'Low';
        } else {
            return 'Minimal';
        }
    }
}
