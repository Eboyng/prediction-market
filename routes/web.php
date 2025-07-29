<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Livewire\LandingPageComponent;
use App\Livewire\AnalyticsDashboardComponent;
use App\Livewire\MarketListComponent;
use App\Livewire\MarketDetailComponent;
use App\Livewire\StakeFormComponent;
use App\Livewire\WalletBalanceComponent;
use App\Livewire\KycUploadComponent;
use App\Livewire\ReferralInviteComponent;
use App\Livewire\ReferralStatusComponent;
use App\Livewire\PromoRedeemComponent;
use App\Livewire\UserSettingsComponent;
use App\Livewire\NotificationCenterComponent;
use App\Livewire\DarkModeToggleComponent;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Routes
Route::get('/', LandingPageComponent::class)->name('home');

// Legal Pages
Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');

// Include authentication routes
require __DIR__.'/auth.php';

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', AnalyticsDashboardComponent::class)->name('dashboard');
    
    // Markets
    Route::get('/markets', MarketListComponent::class)->name('markets.index');
    Route::get('/markets/{market}', MarketDetailComponent::class)->name('markets.show');
    Route::get('/markets/{market}/stake', StakeFormComponent::class)->name('markets.stake');
    
    // User Profile & Settings
    Route::get('/profile', UserSettingsComponent::class)->name('profile.show');
    Route::get('/settings', UserSettingsComponent::class)->name('settings');
    
    // Wallet & Payments
    Route::get('/wallet', WalletBalanceComponent::class)->name('wallet.index');
    Route::post('/wallet/topup', [PaymentController::class, 'topUp'])->name('wallet.topup');
    Route::post('/wallet/withdraw', [PaymentController::class, 'withdraw'])->name('wallet.withdraw');
    
    // Payment Webhooks (no auth required for webhooks)
    Route::post('/webhooks/paystack', [PaymentController::class, 'paystackWebhook'])->name('webhooks.paystack')->withoutMiddleware(['auth', 'verified']);
    Route::post('/webhooks/flutterwave', [PaymentController::class, 'flutterwaveWebhook'])->name('webhooks.flutterwave')->withoutMiddleware(['auth', 'verified']);
    
    // KYC
    Route::get('/kyc', KycUploadComponent::class)->name('kyc.index');
    Route::get('/kyc/upload', KycUploadComponent::class)->name('kyc.upload');
    
    // Referrals
    Route::get('/referrals', ReferralStatusComponent::class)->name('referrals.index');
    Route::get('/referrals/invite', ReferralInviteComponent::class)->name('referrals.invite');
    
    // Promotions
    Route::get('/promos', PromoRedeemComponent::class)->name('promos.index');
    Route::get('/promos/redeem', PromoRedeemComponent::class)->name('promos.redeem');
    
    // Notifications
    Route::get('/notifications', NotificationCenterComponent::class)->name('notifications.index');
    
    // Import/Export
    Route::get('/import', function () {
        return view('import.index');
    })->name('import.index');
    
    // API Routes for AJAX/Livewire components
    Route::prefix('api')->group(function () {
        // Market data endpoints
        Route::get('/markets/{market}/odds', function ($market) {
            $market = \App\Models\Market::findOrFail($market);
            return response()->json([
                'odds' => $market->current_odds,
                'volume' => $market->total_volume,
                'participants' => $market->participant_count
            ]);
        })->name('api.markets.odds');
        
        // User stats
        Route::get('/user/stats', function () {
            $user = \Illuminate\Support\Facades\Auth::user();
            return response()->json([
                'balance' => $user->wallet->balance ?? 0,
                'total_stakes' => $user->stakes()->sum('amount'),
                'total_winnings' => $user->stakes()->where('status', 'won')->sum('payout_amount'),
                'active_stakes' => $user->stakes()->where('status', 'active')->count()
            ]);
        })->name('api.user.stats');
        
        // Notifications
        Route::get('/notifications/unread', function () {
            $user = \Illuminate\Support\Facades\Auth::user();
            return response()->json([
                'count' => $user->unreadNotifications()->count(),
                'notifications' => $user->unreadNotifications()->limit(5)->get()
            ]);
        })->name('api.notifications.unread');
        
        Route::post('/notifications/{id}/read', function ($id) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $user->notifications()->where('id', $id)->update(['read_at' => now()]);
            return response()->json(['success' => true]);
        })->name('api.notifications.read');
    });
});

// Admin Routes (if Filament is being used)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Filament admin panel routes are automatically registered
    // Additional custom admin routes can be added here if needed
});

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment()
    ]);
})->name('health');

// Fallback Route for SPA-like behavior
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
