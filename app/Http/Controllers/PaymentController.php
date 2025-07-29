<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /**
     * Handle deposit/top-up requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:1000000', // Min ₦1, Max ₦10,000
            'payment_method' => 'required|in:paystack,flutterwave',
            'reference' => 'nullable|string|max:100',
        ]);

        try {
            $user = Auth::user();
            $amountInKobo = (int) ($request->amount * 100);

            // Log deposit initiation
            $user->logActivity('deposit_initiated', [
                'amount' => $amountInKobo,
                'amount_naira' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference' => $request->reference,
                'ip_address' => $request->ip(),
            ]);

            // In a real implementation, you would:
            // 1. Create a payment intent with Paystack/Flutterwave
            // 2. Return the payment URL or authorization data
            // 3. Handle the webhook confirmation

            // For demo purposes, we'll simulate a successful payment
            $reference = $request->reference ?? 'demo_' . uniqid();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment initiated successfully',
                'data' => [
                    'reference' => $reference,
                    'amount' => $request->amount,
                    'payment_url' => 'https://demo-payment-gateway.com/pay/' . $reference,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Deposit initiation failed', [
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate payment. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle withdrawal requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:1000000', // Min ₦1, Max ₦10,000
            'bank_code' => 'required|string|max:10',
            'account_number' => 'required|string|max:20',
            'account_name' => 'required|string|max:100',
        ]);

        try {
            $user = Auth::user();
            $amountInKobo = (int) ($request->amount * 100);

            // Validate withdrawal rules
            $this->validateWithdrawalRules($user, $amountInKobo);

            DB::transaction(function () use ($user, $amountInKobo, $request) {
                // Deduct funds from wallet
                $user->deductFunds($amountInKobo, 'Withdrawal request');

                // Log withdrawal request
                $user->logActivity('withdrawal_requested', [
                    'amount' => $amountInKobo,
                    'amount_naira' => $request->amount,
                    'bank_code' => $request->bank_code,
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                    'ip_address' => $request->ip(),
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Withdrawal request submitted successfully. Funds will be processed within 24 hours.',
                'data' => [
                    'amount' => $request->amount,
                    'reference' => 'WD_' . uniqid(),
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Withdrawal request failed', [
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process withdrawal request. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle Paystack webhook
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function paystackWebhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        $computedSignature = hash_hmac('sha512', $payload, config('services.paystack.secret_key'));

        if (!hash_equals($signature, $computedSignature)) {
            Log::warning('Invalid Paystack webhook signature', [
                'signature' => $signature,
                'computed' => $computedSignature,
            ]);
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);
        
        try {
            switch ($event['event']) {
                case 'charge.success':
                    $this->handleSuccessfulPayment($event['data']);
                    break;
                case 'transfer.success':
                    $this->handleSuccessfulWithdrawal($event['data']);
                    break;
                case 'transfer.failed':
                    $this->handleFailedWithdrawal($event['data']);
                    break;
            }

            return response('Webhook processed', 200);
        } catch (\Exception $e) {
            Log::error('Paystack webhook processing failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            return response('Processing failed', 500);
        }
    }

    /**
     * Handle Flutterwave webhook
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function flutterwaveWebhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('verif-hash');
        $secretHash = config('services.flutterwave.secret_hash');

        if ($signature !== $secretHash) {
            Log::warning('Invalid Flutterwave webhook signature');
            return response('Invalid signature', 400);
        }

        $payload = $request->all();
        
        try {
            switch ($payload['event']) {
                case 'charge.completed':
                    if ($payload['data']['status'] === 'successful') {
                        $this->handleSuccessfulPayment($payload['data']);
                    }
                    break;
                case 'transfer.completed':
                    if ($payload['data']['status'] === 'SUCCESSFUL') {
                        $this->handleSuccessfulWithdrawal($payload['data']);
                    } else {
                        $this->handleFailedWithdrawal($payload['data']);
                    }
                    break;
            }

            return response('Webhook processed', 200);
        } catch (\Exception $e) {
            Log::error('Flutterwave webhook processing failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            return response('Processing failed', 500);
        }
    }

    /**
     * Validate withdrawal rules
     *
     * @param User $user
     * @param int $amountInKobo
     * @throws ValidationException
     */
    private function validateWithdrawalRules(User $user, int $amountInKobo): void
    {
        // Check KYC status
        $user->validateKycForWithdrawal();

        // Check minimum deposit requirement for first-time withdrawal
        if (!$user->hasDeposited(500000) && $amountInKobo > 0) { // 5000 Naira in kobo
            throw ValidationException::withMessages([
                'amount' => 'First-time withdrawal requires a ₦5,000 deposit.',
            ]);
        }

        // Check minimum withdrawal amount for returning users
        if ($user->hasDeposited(500000) && $amountInKobo < 100000) { // 1000 Naira in kobo
            throw ValidationException::withMessages([
                'amount' => 'Minimum withdrawal for returning users is ₦1,000.',
            ]);
        }

        // Check sufficient balance
        if (!$user->hasSufficientBalance($amountInKobo)) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient wallet balance.',
            ]);
        }
    }

    /**
     * Handle successful payment
     *
     * @param array $data
     */
    private function handleSuccessfulPayment(array $data): void
    {
        // Extract user info from payment data (this depends on how you store user reference)
        $reference = $data['reference'] ?? $data['tx_ref'] ?? null;
        $amount = $data['amount'] ?? 0;
        
        // In a real implementation, you would:
        // 1. Find the user based on the reference or customer data
        // 2. Add funds to their wallet
        // 3. Send confirmation notifications
        
        Log::info('Payment successful', [
            'reference' => $reference,
            'amount' => $amount,
            'data' => $data,
        ]);

        // Dispatch DepositReceived event
        // event(new DepositReceived($user, $amount, $reference));
    }

    /**
     * Handle successful withdrawal
     *
     * @param array $data
     */
    private function handleSuccessfulWithdrawal(array $data): void
    {
        $reference = $data['reference'] ?? $data['id'] ?? null;
        
        Log::info('Withdrawal successful', [
            'reference' => $reference,
            'data' => $data,
        ]);

        // Dispatch WithdrawalProcessed event
        // event(new WithdrawalProcessed($user, $amount, $reference));
    }

    /**
     * Handle failed withdrawal
     *
     * @param array $data
     */
    private function handleFailedWithdrawal(array $data): void
    {
        $reference = $data['reference'] ?? $data['id'] ?? null;
        
        Log::warning('Withdrawal failed', [
            'reference' => $reference,
            'data' => $data,
        ]);

        // In a real implementation, you would:
        // 1. Find the withdrawal record
        // 2. Refund the amount to user's wallet
        // 3. Send failure notification
    }

    /**
     * Get user's transaction history
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactionHistory(Request $request)
    {
        $user = Auth::user();
        
        $transactions = $user->activityLogs()
            ->whereIn('action', ['deposit_received', 'withdrawal_requested', 'stake_placed', 'payout_received'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }
}
