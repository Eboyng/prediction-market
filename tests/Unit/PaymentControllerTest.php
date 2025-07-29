<?php

namespace Tests\Unit;

use App\Http\Controllers\PaymentController;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ErrorMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Mockery;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentController $paymentController;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->paymentController = new PaymentController();
        $this->user = User::factory()->create([
            'wallet_balance' => 500000, // 5,000 NGN
            'kyc_status' => 'verified'
        ]);
    }

    /** @test */
    public function it_can_initiate_paystack_deposit()
    {
        // Arrange
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test123',
                    'access_code' => 'test_access_code',
                    'reference' => 'test_reference_123'
                ]
            ], 200)
        ]);

        $request = Request::create('/payment/deposit', 'POST', [
            'amount' => 1000000, // 10,000 NGN
            'gateway' => 'paystack'
        ]);

        // Act
        $response = $this->paymentController->initiateDeposit($request, $this->user);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('authorization_url', $responseData['data']);
        
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'type' => 'deposit',
            'amount' => 1000000,
            'status' => 'pending',
            'gateway' => 'paystack'
        ]);
    }

    /** @test */
    public function it_can_initiate_flutterwave_deposit()
    {
        // Arrange
        Http::fake([
            'api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'data' => [
                    'link' => 'https://checkout.flutterwave.com/test123'
                ]
            ], 200)
        ]);

        $request = Request::create('/payment/deposit', 'POST', [
            'amount' => 500000, // 5,000 NGN
            'gateway' => 'flutterwave'
        ]);

        // Act
        $response = $this->paymentController->initiateDeposit($request, $this->user);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('link', $responseData['data']);
        
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'type' => 'deposit',
            'amount' => 500000,
            'status' => 'pending',
            'gateway' => 'flutterwave'
        ]);
    }

    /** @test */
    public function it_validates_minimum_deposit_amount()
    {
        // Arrange
        $request = Request::create('/payment/deposit', 'POST', [
            'amount' => 5000, // 50 NGN (below minimum)
            'gateway' => 'paystack'
        ]);

        // Act
        $response = $this->paymentController->initiateDeposit($request, $this->user);

        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Minimum deposit amount', $responseData['message']);
    }

    /** @test */
    public function it_validates_maximum_deposit_amount()
    {
        // Arrange
        $request = Request::create('/payment/deposit', 'POST', [
            'amount' => 100000000, // 1,000,000 NGN (above maximum)
            'gateway' => 'paystack'
        ]);

        // Act
        $response = $this->paymentController->initiateDeposit($request, $this->user);

        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Maximum deposit amount', $responseData['message']);
    }

    /** @test */
    public function it_handles_paystack_webhook_success()
    {
        // Arrange
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'deposit',
            'amount' => 1000000,
            'status' => 'pending',
            'gateway' => 'paystack',
            'reference' => 'test_ref_123'
        ]);

        $webhookData = [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'test_ref_123',
                'status' => 'success',
                'amount' => 1000000
            ]
        ];

        $request = Request::create('/webhooks/paystack', 'POST', $webhookData);

        // Act
        $response = $this->paymentController->handlePaystackWebhook($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        
        $wallet->refresh();
        $this->assertEquals('completed', $wallet->status);
        
        $this->user->refresh();
        $this->assertEquals(1500000, $this->user->wallet_balance); // 5,000 + 10,000 NGN
    }

    /** @test */
    public function it_handles_paystack_webhook_failure()
    {
        // Arrange
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'deposit',
            'amount' => 1000000,
            'status' => 'pending',
            'gateway' => 'paystack',
            'reference' => 'test_ref_456'
        ]);

        $webhookData = [
            'event' => 'charge.failed',
            'data' => [
                'reference' => 'test_ref_456',
                'status' => 'failed'
            ]
        ];

        $request = Request::create('/webhooks/paystack', 'POST', $webhookData);

        // Act
        $response = $this->paymentController->handlePaystackWebhook($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        
        $wallet->refresh();
        $this->assertEquals('failed', $wallet->status);
        
        $this->user->refresh();
        $this->assertEquals(500000, $this->user->wallet_balance); // Unchanged
    }

    /** @test */
    public function it_can_initiate_withdrawal()
    {
        // Arrange
        $this->user->update(['wallet_balance' => 2000000]); // 20,000 NGN
        
        $request = Request::create('/payment/withdraw', 'POST', [
            'amount' => 1000000, // 10,000 NGN
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Act
        $response = $this->paymentController->initiateWithdrawal($request, $this->user);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 1000000,
            'status' => 'pending'
        ]);
        
        $this->user->refresh();
        $this->assertEquals(1000000, $this->user->wallet_balance); // 20,000 - 10,000 NGN
    }

    /** @test */
    public function it_enforces_minimum_withdrawal_amount()
    {
        // Arrange
        $request = Request::create('/payment/withdraw', 'POST', [
            'amount' => 50000, // 500 NGN (below minimum)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Act
        $response = $this->paymentController->initiateWithdrawal($request, $this->user);

        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Minimum withdrawal amount', $responseData['message']);
    }

    /** @test */
    public function it_enforces_daily_withdrawal_limit()
    {
        // Arrange
        $this->user->update(['wallet_balance' => 10000000]); // 100,000 NGN
        
        // Create existing withdrawal today
        Wallet::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 4000000, // 40,000 NGN
            'status' => 'completed',
            'created_at' => now()
        ]);

        $request = Request::create('/payment/withdraw', 'POST', [
            'amount' => 2000000, // 20,000 NGN (would exceed daily limit of 50,000 NGN)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Act
        $response = $this->paymentController->initiateWithdrawal($request, $this->user);

        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Daily withdrawal limit', $responseData['message']);
    }

    /** @test */
    public function it_requires_kyc_verification_for_large_withdrawals()
    {
        // Arrange
        $this->user->update([
            'wallet_balance' => 10000000, // 100,000 NGN
            'kyc_status' => 'pending'
        ]);

        $request = Request::create('/payment/withdraw', 'POST', [
            'amount' => 2000000, // 20,000 NGN (above KYC threshold)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Act
        $response = $this->paymentController->initiateWithdrawal($request, $this->user);

        // Assert
        $this->assertEquals(403, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertContains('KYC verification required', $responseData['message']);
    }

    /** @test */
    public function it_validates_bank_account_details()
    {
        // Arrange
        Http::fake([
            'api.paystack.co/bank/resolve' => Http::response([
                'status' => false,
                'message' => 'Invalid account number'
            ], 400)
        ]);

        $request = Request::create('/payment/withdraw', 'POST', [
            'amount' => 1000000,
            'bank_code' => '058',
            'account_number' => 'invalid123',
            'account_name' => 'John Doe'
        ]);

        // Act
        $response = $this->paymentController->initiateWithdrawal($request, $this->user);

        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Invalid bank account', $responseData['message']);
    }

    /** @test */
    public function it_handles_insufficient_balance_for_withdrawal()
    {
        // Arrange
        $this->user->update(['wallet_balance' => 500000]); // 5,000 NGN
        
        $request = Request::create('/payment/withdraw', 'POST', [
            'amount' => 1000000, // 10,000 NGN (more than balance)
            'bank_code' => '058',
            'account_number' => '1234567890',
            'account_name' => 'John Doe'
        ]);

        // Act
        $response = $this->paymentController->initiateWithdrawal($request, $this->user);

        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Insufficient balance', $responseData['message']);
    }

    /** @test */
    public function it_logs_payment_errors_to_monitoring_service()
    {
        // Arrange
        $mockErrorService = Mockery::mock(ErrorMonitoringService::class);
        $mockErrorService->shouldReceive('reportPaymentError')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('array'), Mockery::type(User::class));

        $this->app->instance(ErrorMonitoringService::class, $mockErrorService);

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'API Error'
            ], 500)
        ]);

        $request = Request::create('/payment/deposit', 'POST', [
            'amount' => 1000000,
            'gateway' => 'paystack'
        ]);

        // Act
        $response = $this->paymentController->initiateDeposit($request, $this->user);

        // Assert
        $this->assertEquals(500, $response->getStatusCode());
    }

    /** @test */
    public function it_creates_activity_logs_for_transactions()
    {
        // Arrange
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test123',
                    'access_code' => 'test_access_code',
                    'reference' => 'test_reference_123'
                ]
            ], 200)
        ]);

        $request = Request::create('/payment/deposit', 'POST', [
            'amount' => 1000000,
            'gateway' => 'paystack'
        ]);

        // Act
        $response = $this->paymentController->initiateDeposit($request, $this->user);

        // Assert
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'deposit_initiated',
            'description' => 'Deposit of ₦10,000.00 initiated via paystack'
        ]);
    }

    /** @test */
    public function it_handles_webhook_signature_verification()
    {
        // Arrange
        $webhookData = [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'test_ref_123',
                'status' => 'success'
            ]
        ];

        $request = Request::create('/webhooks/paystack', 'POST', $webhookData);
        $request->headers->set('x-paystack-signature', 'invalid_signature');

        // Act
        $response = $this->paymentController->handlePaystackWebhook($request);

        // Assert
        $this->assertEquals(400, $response->getStatusCode());
    }

    /** @test */
    public function it_prevents_duplicate_webhook_processing()
    {
        // Arrange
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'deposit',
            'amount' => 1000000,
            'status' => 'completed', // Already processed
            'gateway' => 'paystack',
            'reference' => 'test_ref_123'
        ]);

        $webhookData = [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'test_ref_123',
                'status' => 'success'
            ]
        ];

        $request = Request::create('/webhooks/paystack', 'POST', $webhookData);

        // Act
        $response = $this->paymentController->handlePaystackWebhook($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        
        // Balance should not change again
        $this->user->refresh();
        $this->assertEquals(500000, $this->user->wallet_balance); // Original balance
    }
}
