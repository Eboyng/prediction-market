<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\SessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.guest')]
#[Title('Login - Prediction Market')]
class LoginComponent extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showPassword = false;
    public bool $isLoading = false;
    
    protected SessionService $sessionService;

    public function boot(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required',
        ];
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $this->isLoading = true;
        
        try {
            // Rate limiting
            $key = 'login.' . request()->ip();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                throw ValidationException::withMessages([
                    'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ]);
            }

            $this->validate();

            // Find user by email
            $user = User::where('email', $this->email)->first();

            // Check credentials
            if (!$user || !Hash::check($this->password, $user->password)) {
                RateLimiter::hit($key, 300); // 5 minutes penalty
                
                throw ValidationException::withMessages([
                    'email' => 'The provided credentials are incorrect.',
                ]);
            }

            // Check if account is verified (optional enforcement)
            if (env('REQUIRE_EMAIL_VERIFICATION', false) && !$user->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    'email' => 'Please verify your email address before logging in.',
                ]);
            }

            // Detect suspicious activity
            $suspiciousActivity = $this->sessionService->detectSuspiciousActivity($user, request());
            
            if ($suspiciousActivity['is_suspicious'] && $suspiciousActivity['risk_level'] === 'high') {
                // Log suspicious login attempt
                $user->logActivity('suspicious_login_attempt', [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'risk_factors' => $suspiciousActivity['factors'],
                    'risk_level' => $suspiciousActivity['risk_level'],
                ]);

                // You might want to require additional verification here
                session()->flash('warning', 'Unusual login activity detected. Please verify your identity.');
            }

            // Login the user
            Auth::login($user, $this->remember);

            // Create session record
            $this->sessionService->createSession($user, request());

            // Clear rate limiter
            RateLimiter::clear($key);

            // Regenerate session for security
            request()->session()->regenerate();

            session()->flash('success', 'Welcome back! You have been successfully logged in.');
            
            // Redirect to intended page or dashboard
            return redirect()->intended(route('dashboard'));

        } catch (ValidationException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Login failed. Please try again.');
            logger()->error('Login failed', [
                'error' => $e->getMessage(),
                'email' => $this->email,
                'ip' => request()->ip()
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.auth.login-component');
    }
}
