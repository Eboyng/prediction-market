<?php

namespace App\Jobs;

use App\Models\Market;
use App\Models\Stake;
use App\Models\User;
use App\Services\OddsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SettlementJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // This job runs without parameters to process all eligible markets
    }

    /**
     * Execute the job - Process all markets ready for settlement
     */
    public function handle(): void
    {
        Log::info('SettlementJob started', ['timestamp' => now()]);

        try {
            // Get all markets that need to be resolved
            $marketsToResolve = Market::where('status', 'open')
                ->where('closes_at', '<=', now())
                ->get();

            Log::info('Markets found for settlement', [
                'count' => $marketsToResolve->count(),
                'market_ids' => $marketsToResolve->pluck('id')->toArray()
            ]);

            foreach ($marketsToResolve as $market) {
                $this->settleMarket($market);
            }

            Log::info('SettlementJob completed successfully', [
                'processed_markets' => $marketsToResolve->count(),
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('SettlementJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);
            
            throw $e;
        }
    }

    /**
     * Settle a specific market
     *
     * @param Market $market
     * @return void
     */
    private function settleMarket(Market $market): void
    {
        Log::info('Starting market settlement', [
            'market_id' => $market->id,
            'question' => $market->question,
            'closes_at' => $market->closes_at
        ]);

        DB::transaction(function () use ($market) {
            // For now, we'll implement a simple random resolution
            // In a real application, this would be based on actual outcomes
            $winningOutcome = $this->determineWinningOutcome($market);
            
            Log::info('Market outcome determined', [
                'market_id' => $market->id,
                'winning_outcome' => $winningOutcome
            ]);

            // Get all stakes for this market
            $allStakes = $market->stakes()->with('user')->get();
            $winningStakes = $allStakes->where('side', $winningOutcome);
            $losingStakes = $allStakes->where('side', '!=', $winningOutcome);

            $totalWinningAmount = $winningStakes->sum('amount');
            $totalLosingAmount = $losingStakes->sum('amount');
            $totalPool = $totalWinningAmount + $totalLosingAmount;

            // Calculate protocol fee (2%)
            $protocolFee = (int) ($totalPool * 0.02);
            $payoutPool = $totalPool - $protocolFee;

            Log::info('Market settlement calculations', [
                'market_id' => $market->id,
                'total_pool' => $totalPool,
                'total_winning_amount' => $totalWinningAmount,
                'total_losing_amount' => $totalLosingAmount,
                'protocol_fee' => $protocolFee,
                'payout_pool' => $payoutPool,
                'winning_stakes_count' => $winningStakes->count(),
                'losing_stakes_count' => $losingStakes->count()
            ]);

            // Distribute payouts to winning stakes
            if ($winningStakes->count() > 0 && $totalWinningAmount > 0) {
                foreach ($winningStakes as $stake) {
                    $this->processWinningStake($stake, $payoutPool, $totalWinningAmount, $market);
                }
            }

            // Log losing stakes for record keeping
            foreach ($losingStakes as $stake) {
                $this->processLosingStake($stake, $market);
            }

            // Update market status
            $market->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'winning_outcome' => $winningOutcome,
                'total_pool' => $totalPool,
                'protocol_fee' => $protocolFee,
            ]);

            // Log market resolution activity
            $this->logMarketResolution($market, $winningOutcome, $totalPool, $protocolFee);

            Log::info('Market settlement completed', [
                'market_id' => $market->id,
                'winning_outcome' => $winningOutcome,
                'total_payouts' => $payoutPool
            ]);
        });
    }

    /**
     * Determine the winning outcome for a market
     * In a real application, this would be based on actual data/oracles
     *
     * @param Market $market
     * @return string
     */
    private function determineWinningOutcome(Market $market): string
    {
        // For demo purposes, we'll use a weighted random based on stake distribution
        $yesStakes = $market->stakes()->where('side', 'yes')->sum('amount');
        $noStakes = $market->stakes()->where('side', 'no')->sum('amount');
        $totalStakes = $yesStakes + $noStakes;

        if ($totalStakes === 0) {
            // If no stakes, random 50/50
            return rand(0, 1) ? 'yes' : 'no';
        }

        // Weight the random outcome based on stake distribution
        $yesWeight = $yesStakes / $totalStakes;
        $random = mt_rand() / mt_getrandmax();
        
        // Add some randomness to prevent predictability
        $adjustedWeight = $yesWeight * 0.7 + 0.15; // Compress to 15%-85% range
        
        return $random < $adjustedWeight ? 'yes' : 'no';
    }

    /**
     * Process a winning stake and distribute payout
     *
     * @param Stake $stake
     * @param int $payoutPool
     * @param int $totalWinningAmount
     * @param Market $market
     * @return void
     */
    private function processWinningStake(Stake $stake, int $payoutPool, int $totalWinningAmount, Market $market): void
    {
        // Calculate proportional payout
        $stakeRatio = $stake->amount / $totalWinningAmount;
        $payout = (int) ($payoutPool * $stakeRatio);
        
        // Ensure minimum payout is the original stake amount
        $payout = max($payout, $stake->amount);
        
        // Add payout to user's wallet
        $stake->user->addFunds($payout, "Winning payout from: {$market->question}");
        
        // Log the payout activity
        $stake->user->logActivity('payout_received', [
            'stake_id' => $stake->id,
            'market_id' => $market->id,
            'market_question' => $market->question,
            'stake_amount' => $stake->amount,
            'stake_amount_naira' => $stake->amount / 100,
            'payout_amount' => $payout,
            'payout_amount_naira' => $payout / 100,
            'profit' => $payout - $stake->amount,
            'profit_naira' => ($payout - $stake->amount) / 100,
            'odds' => $stake->odds_at_placement,
            'side' => $stake->side,
        ]);

        Log::info('Winning stake processed', [
            'stake_id' => $stake->id,
            'user_id' => $stake->user_id,
            'stake_amount' => $stake->amount,
            'payout_amount' => $payout,
            'profit' => $payout - $stake->amount
        ]);
    }

    /**
     * Process a losing stake for record keeping
     *
     * @param Stake $stake
     * @param Market $market
     * @return void
     */
    private function processLosingStake(Stake $stake, Market $market): void
    {
        // Log the loss activity
        $stake->user->logActivity('stake_lost', [
            'stake_id' => $stake->id,
            'market_id' => $market->id,
            'market_question' => $market->question,
            'stake_amount' => $stake->amount,
            'stake_amount_naira' => $stake->amount / 100,
            'odds' => $stake->odds_at_placement,
            'side' => $stake->side,
        ]);

        Log::info('Losing stake processed', [
            'stake_id' => $stake->id,
            'user_id' => $stake->user_id,
            'stake_amount' => $stake->amount,
            'side' => $stake->side
        ]);
    }

    /**
     * Log market resolution activity
     *
     * @param Market $market
     * @param string $winningOutcome
     * @param int $totalPool
     * @param int $protocolFee
     * @return void
     */
    private function logMarketResolution(Market $market, string $winningOutcome, int $totalPool, int $protocolFee): void
    {
        // Create a system activity log entry
        \App\Models\ActivityLog::create([
            'user_id' => 1, // System user ID (you might want to create a system user)
            'action' => 'market_resolved',
            'metadata' => json_encode([
                'market_id' => $market->id,
                'market_question' => $market->question,
                'winning_outcome' => $winningOutcome,
                'total_pool' => $totalPool,
                'total_pool_naira' => $totalPool / 100,
                'protocol_fee' => $protocolFee,
                'protocol_fee_naira' => $protocolFee / 100,
                'resolved_at' => now(),
                'closes_at' => $market->closes_at,
            ])
        ]);
    }

    /**
     * Handle job failure
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SettlementJob failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()
        ]);
    }
}
