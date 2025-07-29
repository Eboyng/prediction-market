<?php

namespace App\Livewire;

use App\Models\User;
use App\Rules\PasswordStrength;
use App\Services\ErrorMonitoringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Account Settings')]
class UserSettingsComponent extends Component
{
    use WithFileUploads;

    // Profile Information
    public $name;
    public $email;
    public $phone;
    public $bio;
    public $avatar;
    public $current_avatar;

    // Password Change
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public $show_current_password = false;
    public $show_new_password = false;
    public $password_strength = 0;

    // Notification Preferences
    public $email_notifications = true;
    public $sms_notifications = true;
    public $push_notifications = true;
    public $marketing_emails = false;
    
    // Specific notification types
    public $notify_market_updates = true;
    public $notify_stake_results = true;
    public $notify_deposits = true;
    public $notify_withdrawals = true;
    public $notify_referrals = true;
    public $notify_promotions = false;

    // Privacy & Security
    public $two_factor_enabled = false;
    public $session_timeout = 120; // minutes
    public $login_notifications = true;

    // Display Preferences
    public $dark_mode = false;
    public $language = 'en';
    public $timezone = 'Africa/Lagos';
    public $currency_display = 'NGN';

    // Active Tab
    public $activeTab = 'profile';

    protected ErrorMonitoringService $errorService;

    public function boot(ErrorMonitoringService $errorService)
    {
        $this->errorService = $errorService;
    }

    public function mount()
    {
        $user = Auth::user();
        
        // Load profile data
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->bio = $user->bio;
        $this->current_avatar = $user->avatar;

        // Load notification preferences
        $preferences = $user->notification_preferences ?? [];
        $this->email_notifications = $preferences['email'] ?? true;
        $this->sms_notifications = $preferences['sms'] ?? true;
        $this->push_notifications = $preferences['push'] ?? true;
        $this->marketing_emails = $preferences['marketing'] ?? false;
        
        $this->notify_market_updates = $preferences['market_updates'] ?? true;
        $this->notify_stake_results = $preferences['stake_results'] ?? true;
        $this->notify_deposits = $preferences['deposits'] ?? true;
        $this->notify_withdrawals = $preferences['withdrawals'] ?? true;
        $this->notify_referrals = $preferences['referrals'] ?? true;
        $this->notify_promotions = $preferences['promotions'] ?? false;

        // Load security settings
        $this->two_factor_enabled = $user->two_factor_enabled ?? false;
        $this->login_notifications = $user->login_notifications ?? true;

        // Load display preferences
        $this->dark_mode = $user->dark_mode ?? false;
        $this->language = $user->language ?? 'en';
        $this->timezone = $user->timezone ?? 'Africa/Lagos';
    }

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore(Auth::id())],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
        ];

        if ($this->activeTab === 'password') {
            $rules = array_merge($rules, [
                'current_password' => ['required', 'current_password'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed', new PasswordStrength()],
                'new_password_confirmation' => ['required'],
            ]);
        }

        return $rules;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetValidation();
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore(Auth::id())],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            $user = Auth::user();
            
            // Handle avatar upload
            if ($this->avatar) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                $avatarPath = $this->avatar->store('avatars', 'public');
                $user->avatar = $avatarPath;
                $this->current_avatar = $avatarPath;
            }

            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'bio' => $this->bio,
                'avatar' => $user->avatar,
            ]);

            // Log activity
            $user->logActivity('profile_updated', [
                'fields_updated' => ['name', 'email', 'phone', 'bio'],
                'avatar_updated' => $this->avatar ? true : false,
            ]);

            $this->avatar = null; // Reset file input
            session()->flash('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            $this->errorService->reportException($e, [
                'user_id' => Auth::id(),
                'action' => 'update_profile',
            ]);
            
            session()->flash('error', 'Failed to update profile. Please try again.');
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', new PasswordStrength()],
            'new_password_confirmation' => ['required'],
        ]);

        try {
            $user = Auth::user();
            $user->update([
                'password' => Hash::make($this->new_password),
            ]);

            // Log activity
            $user->logActivity('password_changed', [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Reset password fields
            $this->current_password = '';
            $this->new_password = '';
            $this->new_password_confirmation = '';
            $this->password_strength = 0;

            session()->flash('success', 'Password updated successfully!');

        } catch (\Exception $e) {
            $this->errorService->reportException($e, [
                'user_id' => Auth::id(),
                'action' => 'update_password',
            ]);
            
            session()->flash('error', 'Failed to update password. Please try again.');
        }
    }

    public function updateNotificationPreferences()
    {
        try {
            $preferences = [
                'email' => $this->email_notifications,
                'sms' => $this->sms_notifications,
                'push' => $this->push_notifications,
                'marketing' => $this->marketing_emails,
                'market_updates' => $this->notify_market_updates,
                'stake_results' => $this->notify_stake_results,
                'deposits' => $this->notify_deposits,
                'withdrawals' => $this->notify_withdrawals,
                'referrals' => $this->notify_referrals,
                'promotions' => $this->notify_promotions,
            ];

            $user = Auth::user();
            $user->update([
                'notification_preferences' => $preferences,
            ]);

            // Log activity
            $user->logActivity('notification_preferences_updated', [
                'preferences' => $preferences,
            ]);

            session()->flash('success', 'Notification preferences updated successfully!');

        } catch (\Exception $e) {
            $this->errorService->reportException($e, [
                'user_id' => Auth::id(),
                'action' => 'update_notification_preferences',
            ]);
            
            session()->flash('error', 'Failed to update notification preferences. Please try again.');
        }
    }

    public function updateSecuritySettings()
    {
        try {
            $user = Auth::user();
            $user->update([
                'two_factor_enabled' => $this->two_factor_enabled,
                'login_notifications' => $this->login_notifications,
            ]);

            // Log activity
            $user->logActivity('security_settings_updated', [
                'two_factor_enabled' => $this->two_factor_enabled,
                'login_notifications' => $this->login_notifications,
            ]);

            session()->flash('success', 'Security settings updated successfully!');

        } catch (\Exception $e) {
            $this->errorService->reportException($e, [
                'user_id' => Auth::id(),
                'action' => 'update_security_settings',
            ]);
            
            session()->flash('error', 'Failed to update security settings. Please try again.');
        }
    }

    public function updateDisplayPreferences()
    {
        try {
            $user = Auth::user();
            $user->update([
                'dark_mode' => $this->dark_mode,
                'language' => $this->language,
                'timezone' => $this->timezone,
            ]);

            // Log activity
            $user->logActivity('display_preferences_updated', [
                'dark_mode' => $this->dark_mode,
                'language' => $this->language,
                'timezone' => $this->timezone,
            ]);

            // Set dark mode cookie for immediate effect
            setcookie('darkMode', $this->dark_mode ? 'true' : 'false', time() + (365 * 24 * 60 * 60), '/');

            session()->flash('success', 'Display preferences updated successfully!');
            
            // Refresh page to apply dark mode
            return redirect()->route('settings');

        } catch (\Exception $e) {
            $this->errorService->reportException($e, [
                'user_id' => Auth::id(),
                'action' => 'update_display_preferences',
            ]);
            
            session()->flash('error', 'Failed to update display preferences. Please try again.');
        }
    }

    public function togglePasswordVisibility($field)
    {
        if ($field === 'current') {
            $this->show_current_password = !$this->show_current_password;
        } elseif ($field === 'new') {
            $this->show_new_password = !$this->show_new_password;
        }
    }

    public function updatedNewPassword()
    {
        if ($this->new_password) {
            $passwordRule = new PasswordStrength();
            $this->password_strength = $passwordRule->calculateStrength($this->new_password);
        } else {
            $this->password_strength = 0;
        }
    }

    public function deleteAvatar()
    {
        try {
            $user = Auth::user();
            
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
                $user->update(['avatar' => null]);
                $this->current_avatar = null;

                // Log activity
                $user->logActivity('avatar_deleted');

                session()->flash('success', 'Avatar deleted successfully!');
            }

        } catch (\Exception $e) {
            $this->errorService->reportException($e, [
                'user_id' => Auth::id(),
                'action' => 'delete_avatar',
            ]);
            
            session()->flash('error', 'Failed to delete avatar. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.user-settings-component');
    }
}
