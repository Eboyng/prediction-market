<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SettlementJob;
use App\Models\Market;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

// Inspiring quote command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Market settlement command
Artisan::command('markets:settle', function () {
    $this->info('Starting market settlement process...');
    
    $closedMarkets = Market::where('status', 'open')
        ->where('closes_at', '<=', now())
        ->get();
    
    $this->info("Found {$closedMarkets->count()} markets to settle.");
    
    foreach ($closedMarkets as $market) {
        try {
            SettlementJob::dispatch($market);
            $this->line("Queued settlement for market: {$market->question}");
        } catch (Exception $e) {
            $this->error("Failed to queue settlement for market {$market->id}: {$e->getMessage()}");
            Log::error('Market settlement queue failed', [
                'market_id' => $market->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    $this->info('Market settlement process completed.');
})->purpose('Settle closed markets and process payouts');

// Analytics cache refresh command
Artisan::command('analytics:refresh', function (AnalyticsService $analyticsService) {
    $this->info('Refreshing analytics cache...');
    
    try {
        // Clear existing analytics cache
        Cache::tags(['analytics'])->flush();
        
        // Refresh platform overview
        $analyticsService->getPlatformOverview();
        $this->line('✓ Platform overview refreshed');
        
        // Refresh user analytics for active users
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(7))->limit(100)->get();
        foreach ($activeUsers as $user) {
            $analyticsService->getUserAnalytics($user->id);
        }
        $this->line("✓ User analytics refreshed for {$activeUsers->count()} active users");
        
        // Refresh market analytics
        $activeMarkets = Market::where('status', 'open')->limit(50)->get();
        foreach ($activeMarkets as $market) {
            $analyticsService->getMarketAnalytics($market->id);
        }
        $this->line("✓ Market analytics refreshed for {$activeMarkets->count()} active markets");
        
        // Refresh financial analytics
        $analyticsService->getFinancialAnalytics();
        $this->line('✓ Financial analytics refreshed');
        
        $this->info('Analytics cache refresh completed successfully.');
    } catch (Exception $e) {
        $this->error("Analytics refresh failed: {$e->getMessage()}");
        Log::error('Analytics refresh failed', ['error' => $e->getMessage()]);
    }
})->purpose('Refresh analytics cache for better performance');

// Database cleanup command
Artisan::command('db:cleanup', function () {
    $this->info('Starting database cleanup...');
    
    try {
        // Clean old activity logs (older than 6 months)
        $deletedLogs = ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();
        $this->line("✓ Deleted {$deletedLogs} old activity logs");
        
        // Clean expired sessions
        DB::table('sessions')->where('last_activity', '<', now()->subDays(30)->timestamp)->delete();
        $this->line('✓ Cleaned expired sessions');
        
        // Clean old notifications (read notifications older than 3 months)
        DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subMonths(3))
            ->delete();
        $this->line('✓ Cleaned old read notifications');
        
        // Clean old password reset tokens
        DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours(24))
            ->delete();
        $this->line('✓ Cleaned expired password reset tokens');
        
        $this->info('Database cleanup completed successfully.');
    } catch (Exception $e) {
        $this->error("Database cleanup failed: {$e->getMessage()}");
        Log::error('Database cleanup failed', ['error' => $e->getMessage()]);
    }
})->purpose('Clean up old database records and optimize storage');

// User statistics update command
Artisan::command('users:update-stats', function () {
    $this->info('Updating user statistics...');
    
    try {
        $users = User::with(['stakes', 'wallet'])->get();
        $updated = 0;
        
        foreach ($users as $user) {
            // Update user statistics
            $totalStakes = $user->stakes()->sum('amount');
            $totalWinnings = $user->stakes()->where('status', 'won')->sum('payout_amount');
            $activeStakes = $user->stakes()->where('status', 'active')->count();
            $winRate = $user->stakes()->where('status', '!=', 'active')->count() > 0 
                ? ($user->stakes()->where('status', 'won')->count() / $user->stakes()->where('status', '!=', 'active')->count()) * 100
                : 0;
            
            // Update user record with calculated stats
            $user->update([
                'total_stakes_amount' => $totalStakes,
                'total_winnings' => $totalWinnings,
                'active_stakes_count' => $activeStakes,
                'win_rate' => round($winRate, 2)
            ]);
            
            $updated++;
        }
        
        $this->info("Updated statistics for {$updated} users.");
    } catch (Exception $e) {
        $this->error("User statistics update failed: {$e->getMessage()}");
        Log::error('User statistics update failed', ['error' => $e->getMessage()]);
    }
})->purpose('Update user statistics and performance metrics');

// System health check command
Artisan::command('system:health-check', function () {
    $this->info('Running system health check...');
    
    $issues = [];
    
    try {
        // Check database connection
        DB::connection()->getPdo();
        $this->line('✓ Database connection: OK');
    } catch (Exception $e) {
        $issues[] = 'Database connection failed';
        $this->error('✗ Database connection: FAILED');
    }
    
    try {
        // Check cache connection
        Cache::put('health_check', 'ok', 60);
        if (Cache::get('health_check') === 'ok') {
            $this->line('✓ Cache system: OK');
        } else {
            $issues[] = 'Cache system not working';
            $this->error('✗ Cache system: FAILED');
        }
    } catch (Exception $e) {
        $issues[] = 'Cache system error';
        $this->error('✗ Cache system: ERROR');
    }
    
    // Check storage permissions
    if (is_writable(storage_path())) {
        $this->line('✓ Storage permissions: OK');
    } else {
        $issues[] = 'Storage not writable';
        $this->error('✗ Storage permissions: FAILED');
    }
    
    // Check queue status
    try {
        $queueSize = DB::table('jobs')->count();
        $this->line("✓ Queue system: OK ({$queueSize} jobs pending)");
        
        if ($queueSize > 1000) {
            $issues[] = 'Queue backlog is high';
            $this->warn('⚠ Queue backlog is high');
        }
    } catch (Exception $e) {
        $issues[] = 'Queue system error';
        $this->error('✗ Queue system: ERROR');
    }
    
    if (empty($issues)) {
        $this->info('✅ System health check completed: All systems operational');
    } else {
        $this->error('❌ System health check completed with issues:');
        foreach ($issues as $issue) {
            $this->error("  - {$issue}");
        }
        Log::warning('System health check found issues', ['issues' => $issues]);
    }
})->purpose('Check system health and report any issues');
