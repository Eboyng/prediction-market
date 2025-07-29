<?php

namespace App\Services;

use App\Models\Market;
use App\Models\Stake;
use App\Models\User;
use App\Models\Wallet;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Analytics Service
 * 
 * Provides comprehensive analytics and reporting functionality
 * for the prediction market platform.
 */
class AnalyticsService
{
    /**
     * Get platform overview statistics
     *
     * @param int $days
     * @return array
     */
    public function getPlatformOverview(int $days = 30): array
    {
        $cacheKey = "platform_overview_{$days}";
        
        return Cache::remember($cacheKey, 300, function () use ($days) { // 5 minutes cache
            $startDate = Carbon::now()->subDays($days);
            
            return [
                'total_users' => User::count(),
                'new_users' => User::where('created_at', '>=', $startDate)->count(),
                'total_markets' => Market::count(),
                'active_markets' => Market::where('status', 'open')->count(),
                'total_stakes' => Stake::count(),
                'total_volume' => Stake::sum('amount'),
                'volume_period' => Stake::where('created_at', '>=', $startDate)->sum('amount'),
                'avg_stake_amount' => Stake::avg('amount'),
                'total_payouts' => Stake::where('status', 'won')->sum('payout_amount'),
                'platform_revenue' => $this->calculatePlatformRevenue($days),
            ];
        });
    }

    /**
     * Get user analytics
     *
     * @param int $days
     * @return array
     */
    public function getUserAnalytics(int $days = 30): array
    {
        $cacheKey = "user_analytics_{$days}";
        
        return Cache::remember($cacheKey, 300, function () use ($days) {
            $startDate = Carbon::now()->subDays($days);
            
            // User registration trends
            $registrationTrends = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            // Active users (users who placed stakes)
            $activeUsers = Stake::select('user_id')
                ->where('created_at', '>=', $startDate)
                ->distinct()
                ->count();

            // User retention
            $retention = $this->calculateUserRetention($days);

            // Top users by volume
            $topUsersByVolume = Stake::select(
                'user_id',
                DB::raw('SUM(amount) as total_volume'),
                DB::raw('COUNT(*) as total_stakes')
            )
            ->with('user:id,name,email')
            ->where('created_at', '>=', $startDate)
            ->groupBy('user_id')
            ->orderBy('total_volume', 'desc')
            ->limit(10)
            ->get();

            return [
                'registration_trends' => $registrationTrends,
                'active_users' => $activeUsers,
                'retention_rate' => $retention,
                'top_users' => $topUsersByVolume,
                'user_segments' => $this->getUserSegments(),
            ];
        });
    }

    /**
     * Get market analytics
     *
     * @param int $days
     * @return array
     */
    public function getMarketAnalytics(int $days = 30): array
    {
        $cacheKey = "market_analytics_{$days}";
        
        return Cache::remember($cacheKey, 300, function () use ($days) {
            $startDate = Carbon::now()->subDays($days);
            
            // Market creation trends
            $marketTrends = Market::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            // Markets by category
            $marketsByCategory = Market::select(
                'categories.name as category',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(COALESCE(stakes_sum.total_amount, 0)) as total_volume')
            )
            ->leftJoin('categories', 'markets.category_id', '=', 'categories.id')
            ->leftJoin(
                DB::raw('(SELECT market_id, SUM(amount) as total_amount FROM stakes GROUP BY market_id) as stakes_sum'),
                'markets.id', '=', 'stakes_sum.market_id'
            )
            ->where('markets.created_at', '>=', $startDate)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('count', 'desc')
            ->get();

            // Market performance
            $marketPerformance = Market::select(
                'markets.id',
                'markets.question',
                'markets.status',
                DB::raw('COUNT(stakes.id) as total_stakes'),
                DB::raw('SUM(stakes.amount) as total_volume'),
                DB::raw('AVG(stakes.amount) as avg_stake')
            )
            ->leftJoin('stakes', 'markets.id', '=', 'stakes.market_id')
            ->where('markets.created_at', '>=', $startDate)
            ->groupBy('markets.id', 'markets.question', 'markets.status')
            ->orderBy('total_volume', 'desc')
            ->limit(10)
            ->get();

            return [
                'market_trends' => $marketTrends,
                'markets_by_category' => $marketsByCategory,
                'top_markets' => $marketPerformance,
                'market_success_rate' => $this->calculateMarketSuccessRate($days),
                'avg_market_duration' => $this->calculateAverageMarketDuration($days),
            ];
        });
    }

    /**
     * Get financial analytics
     *
     * @param int $days
     * @return array
     */
    public function getFinancialAnalytics(int $days = 30): array
    {
        $cacheKey = "financial_analytics_{$days}";
        
        return Cache::remember($cacheKey, 300, function () use ($days) {
            $startDate = Carbon::now()->subDays($days);
            
            // Volume trends
            $volumeTrends = Stake::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as volume'),
                DB::raw('COUNT(*) as stakes_count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            // Revenue breakdown
            $revenueBreakdown = $this->getRevenueBreakdown($days);

            // Wallet analytics
            $walletAnalytics = $this->getWalletAnalytics($days);

            // Payout analytics
            $payoutAnalytics = Stake::select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('SUM(payout_amount) as total_payouts'),
                DB::raw('COUNT(*) as winning_stakes')
            )
            ->where('status', 'won')
            ->where('updated_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            return [
                'volume_trends' => $volumeTrends,
                'revenue_breakdown' => $revenueBreakdown,
                'wallet_analytics' => $walletAnalytics,
                'payout_trends' => $payoutAnalytics,
                'profit_margins' => $this->calculateProfitMargins($days),
            ];
        });
    }

    /**
     * Get real-time dashboard data
     *
     * @return array
     */
    public function getRealTimeDashboard(): array
    {
        $cacheKey = "realtime_dashboard";
        
        return Cache::remember($cacheKey, 60, function () { // 1 minute cache
            return [
                'active_users_now' => $this->getActiveUsersNow(),
                'stakes_last_hour' => Stake::where('created_at', '>=', Carbon::now()->subHour())->count(),
                'volume_last_hour' => Stake::where('created_at', '>=', Carbon::now()->subHour())->sum('amount'),
                'new_markets_today' => Market::whereDate('created_at', Carbon::today())->count(),
                'closing_markets_today' => Market::whereDate('closes_at', Carbon::today())->count(),
                'recent_activities' => $this->getRecentActivities(),
                'trending_markets' => $this->getTrendingMarkets(),
                'system_health' => $this->getSystemHealth(),
            ];
        });
    }

    /**
     * Calculate platform revenue
     *
     * @param int $days
     * @return float
     */
    protected function calculatePlatformRevenue(int $days): float
    {
        $startDate = Carbon::now()->subDays($days);
        
        // Platform takes a percentage of each stake as fee
        $feePercentage = config('app.platform_fee_percentage', 2.5) / 100;
        
        return Stake::where('created_at', '>=', $startDate)
            ->sum('amount') * $feePercentage;
    }

    /**
     * Calculate user retention rate
     *
     * @param int $days
     * @return float
     */
    protected function calculateUserRetention(int $days): float
    {
        $startDate = Carbon::now()->subDays($days);
        $midDate = Carbon::now()->subDays($days / 2);
        
        $earlyUsers = User::where('created_at', '>=', $startDate)
            ->where('created_at', '<', $midDate)
            ->pluck('id');
        
        if ($earlyUsers->isEmpty()) {
            return 0;
        }
        
        $returnedUsers = Stake::whereIn('user_id', $earlyUsers)
            ->where('created_at', '>=', $midDate)
            ->distinct('user_id')
            ->count();
        
        return ($returnedUsers / $earlyUsers->count()) * 100;
    }

    /**
     * Get user segments
     *
     * @return array
     */
    protected function getUserSegments(): array
    {
        return [
            'high_volume' => User::whereHas('stakes', function ($query) {
                $query->havingRaw('SUM(amount) > ?', [1000000]); // 10,000 NGN
            })->count(),
            'medium_volume' => User::whereHas('stakes', function ($query) {
                $query->havingRaw('SUM(amount) BETWEEN ? AND ?', [100000, 1000000]); // 1,000 - 10,000 NGN
            })->count(),
            'low_volume' => User::whereHas('stakes', function ($query) {
                $query->havingRaw('SUM(amount) < ?', [100000]); // < 1,000 NGN
            })->count(),
            'inactive' => User::whereDoesntHave('stakes')->count(),
        ];
    }

    /**
     * Calculate market success rate
     *
     * @param int $days
     * @return float
     */
    protected function calculateMarketSuccessRate(int $days): float
    {
        $startDate = Carbon::now()->subDays($days);
        
        $totalMarkets = Market::where('created_at', '>=', $startDate)->count();
        $settledMarkets = Market::where('created_at', '>=', $startDate)
            ->where('status', 'settled')
            ->count();
        
        return $totalMarkets > 0 ? ($settledMarkets / $totalMarkets) * 100 : 0;
    }

    /**
     * Calculate average market duration
     *
     * @param int $days
     * @return float
     */
    protected function calculateAverageMarketDuration(int $days): float
    {
        $startDate = Carbon::now()->subDays($days);
        
        $avgDuration = Market::where('created_at', '>=', $startDate)
            ->where('status', 'settled')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closes_at)) as avg_hours')
            ->value('avg_hours');
        
        return $avgDuration ?? 0;
    }

    /**
     * Get revenue breakdown
     *
     * @param int $days
     * @return array
     */
    protected function getRevenueBreakdown(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);
        $feePercentage = config('app.platform_fee_percentage', 2.5) / 100;
        
        return [
            'stake_fees' => Stake::where('created_at', '>=', $startDate)->sum('amount') * $feePercentage,
            'withdrawal_fees' => 0, // Implement if you have withdrawal fees
            'premium_features' => 0, // Implement if you have premium features
            'advertising' => 0, // Implement if you have advertising revenue
        ];
    }

    /**
     * Get wallet analytics
     *
     * @param int $days
     * @return array
     */
    protected function getWalletAnalytics(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total_deposits' => Wallet::where('created_at', '>=', $startDate)
                ->where('type', 'deposit')
                ->sum('amount'),
            'total_withdrawals' => Wallet::where('created_at', '>=', $startDate)
                ->where('type', 'withdrawal')
                ->sum('amount'),
            'pending_withdrawals' => Wallet::where('type', 'withdrawal')
                ->where('status', 'pending')
                ->sum('amount'),
            'average_wallet_balance' => User::avg('wallet_balance'),
        ];
    }

    /**
     * Calculate profit margins
     *
     * @param int $days
     * @return array
     */
    protected function calculateProfitMargins(int $days): array
    {
        $revenue = $this->calculatePlatformRevenue($days);
        $payouts = Stake::where('status', 'won')
            ->where('updated_at', '>=', Carbon::now()->subDays($days))
            ->sum('payout_amount');
        
        $grossProfit = $revenue;
        $netProfit = $revenue - ($payouts * 0.1); // Assuming 10% operational costs
        
        return [
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'profit_margin' => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0,
        ];
    }

    /**
     * Get active users now (last 15 minutes)
     *
     * @return int
     */
    protected function getActiveUsersNow(): int
    {
        return ActivityLog::where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->distinct('user_id')
            ->count();
    }

    /**
     * Get recent activities
     *
     * @return Collection
     */
    protected function getRecentActivities(): Collection
    {
        return ActivityLog::with('user:id,name')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get trending markets
     *
     * @return Collection
     */
    protected function getTrendingMarkets(): Collection
    {
        return Market::select('markets.*')
            ->withCount(['stakes' => function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subHours(24));
            }])
            ->where('status', 'open')
            ->orderBy('stakes_count', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get system health metrics
     *
     * @return array
     */
    protected function getSystemHealth(): array
    {
        return [
            'database_connections' => DB::connection()->getPdo() ? 'healthy' : 'unhealthy',
            'cache_status' => Cache::get('health_check') !== null ? 'healthy' : 'unhealthy',
            'queue_status' => 'healthy', // Implement queue health check
            'storage_status' => 'healthy', // Implement storage health check
        ];
    }

    /**
     * Export analytics data
     *
     * @param string $type
     * @param int $days
     * @return array
     */
    public function exportAnalytics(string $type, int $days = 30): array
    {
        switch ($type) {
            case 'users':
                return $this->getUserAnalytics($days);
            case 'markets':
                return $this->getMarketAnalytics($days);
            case 'financial':
                return $this->getFinancialAnalytics($days);
            case 'overview':
            default:
                return $this->getPlatformOverview($days);
        }
    }

    /**
     * Clear analytics cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        $patterns = [
            'platform_overview_*',
            'user_analytics_*',
            'market_analytics_*',
            'financial_analytics_*',
            'realtime_dashboard',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
