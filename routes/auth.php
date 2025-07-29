<?php

use App\Livewire\Auth\LoginComponent;
use App\Livewire\Auth\RegisterComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Here is where you can register authentication routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Guest routes (only accessible when not authenticated)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', LoginComponent::class)->name('login');
    
    // Register
    Route::get('/register', RegisterComponent::class)->name('register');
    
    // Password Reset (if implemented)
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
});

// Authenticated routes (only accessible when authenticated)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', function () {
        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        return redirect('/');
    })->name('logout');
    
    // Email verification routes (if needed)
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', function () {
        // Handle email verification
        return redirect()->route('dashboard');
    })->middleware(['signed'])->name('verification.verify');
    
    Route::post('/email/verification-notification', function () {
        // Resend verification email
        return back()->with('message', 'Verification link sent!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});
