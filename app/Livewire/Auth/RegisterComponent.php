<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Rules\PasswordStrength;
use App\Events\UserRegistered;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.guest')]
#[Title('Register - Prediction Market')]
class RegisterComponent extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $referral_code = '';
    public bool $agree_terms = false;
    public bool $marketing_consent = false;
    
    public array $passwordStrength = [];
    public bool $showPassword = false;
    public bool $isLoading = false;

    public function mount()
    {
        // Pre-fill referral code if provided in URL
        $this->referral_code = request()->get('ref', '');
    }

    public function updatedPassword()
    {
        if (!empty($this->password)) {
            $this->passwordStrength = \App\Rules\PasswordStrength::getStrengthScore($this->password);
        } else {
            $this->passwordStrength = [];
        }
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'regex:/^(\+234|0)[789][01]\d{8}$/', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', new PasswordStrength()],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
            'agree_terms' => ['required', 'accepted'],
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => 'Please enter a valid Nigerian phone number (e.g., +2348012345678 or 08012345678)',
            'agree_terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy',
            'referral_code.exists' => 'Invalid referral code',
        ];
    }

    public function register()
    {
        $this->isLoading = true;
        
        try {
            $this->validate();

            DB::transaction(function () {
                // Create the user
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($this->password),
                    'is_verified' => false,
                    'kyc_status' => 'pending',
                    'dark_mode' => false,
                    'email_notifications' => true,
                    'sms_notifications' => true,
                    'in_app_notifications' => true,
                    'market_updates' => true,
                    'stake_confirmations' => true,
                    'withdrawal_updates' => true,
                    'referral_updates' => true,
                    'promo_notifications' => $this->marketing_consent,
                ]);

                // Generate referral code for new user
                $user->generateReferralCode();

                // Create wallet for user
                $user->wallet()->create(['balance' => 0]);

                // Process referral if provided
                if (!empty($this->referral_code)) {
                    $referrer = User::where('referral_code', $this->referral_code)->first();
                    if ($referrer) {
                        $user->processReferral($referrer);
                    }
                }

                // Log registration activity
                $user->logActivity('registration', [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'referral_code' => $this->referral_code ?: null,
                    'marketing_consent' => $this->marketing_consent,
                ]);

                // Send welcome notification
                $user->notify(new WelcomeNotification($user));

                // Fire registration event
                event(new UserRegistered($user));

                // Auto-login the user
                Auth::login($user);
            });

            session()->flash('success', 'Registration successful! Welcome to our prediction market platform.');
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            session()->flash('error', 'Registration failed. Please try again.');
            logger()->error('Registration failed', ['error' => $e->getMessage(), 'user_data' => $this->only(['name', 'email', 'phone'])]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.auth.register-component');
    }
}
