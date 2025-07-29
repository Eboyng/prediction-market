<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Platform Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettingsData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->schema([
                                Forms\Components\Section::make('Site Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('app_name')
                                            ->label('Site Name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('app_url')
                                            ->label('Site URL')
                                            ->url()
                                            ->required(),
                                        Forms\Components\Textarea::make('site_description')
                                            ->label('Site Description')
                                            ->maxLength(500),
                                        Forms\Components\FileUpload::make('site_logo')
                                            ->label('Site Logo')
                                            ->image()
                                            ->maxSize(2048),
                                        Forms\Components\FileUpload::make('site_favicon')
                                            ->label('Site Favicon')
                                            ->image()
                                            ->maxSize(512),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('Contact Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('admin_email')
                                            ->label('Admin Email')
                                            ->email()
                                            ->required(),
                                        Forms\Components\TextInput::make('support_email')
                                            ->label('Support Email')
                                            ->email()
                                            ->required(),
                                        Forms\Components\TextInput::make('support_phone')
                                            ->label('Support Phone')
                                            ->tel(),
                                        Forms\Components\TextInput::make('admin_name')
                                            ->label('Admin Name')
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Features')
                            ->schema([
                                Forms\Components\Section::make('Feature Toggles')
                                    ->schema([
                                        Forms\Components\Toggle::make('kyc_required')
                                            ->label('KYC Required for Withdrawals'),
                                        Forms\Components\Toggle::make('referral_system_enabled')
                                            ->label('Referral System Enabled'),
                                        Forms\Components\Toggle::make('promo_codes_enabled')
                                            ->label('Promo Codes Enabled'),
                                        Forms\Components\Toggle::make('two_factor_auth_enabled')
                                            ->label('Two-Factor Authentication'),
                                        Forms\Components\Toggle::make('dark_mode_enabled')
                                            ->label('Dark Mode Support'),
                                        Forms\Components\Toggle::make('notifications_enabled')
                                            ->label('Notifications System'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Market Configuration')
                            ->schema([
                                Forms\Components\Section::make('Market Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('min_stake_amount')
                                            ->label('Minimum Stake Amount (Kobo)')
                                            ->numeric()
                                            ->required()
                                            ->helperText('Minimum amount users can stake (in kobo)'),
                                        Forms\Components\TextInput::make('max_stake_amount')
                                            ->label('Maximum Stake Amount (Kobo)')
                                            ->numeric()
                                            ->required()
                                            ->helperText('Maximum amount users can stake (in kobo)'),
                                        Forms\Components\TextInput::make('protocol_fee_percent')
                                            ->label('Protocol Fee (%)')
                                            ->numeric()
                                            ->step(0.1)
                                            ->required()
                                            ->helperText('Platform fee percentage on winnings'),
                                        Forms\Components\TextInput::make('house_edge_percent')
                                            ->label('House Edge (%)')
                                            ->numeric()
                                            ->step(0.1)
                                            ->required()
                                            ->helperText('House edge percentage for odds calculation'),
                                        Forms\Components\TextInput::make('base_liquidity_pool')
                                            ->label('Base Liquidity Pool (Kobo)')
                                            ->numeric()
                                            ->required()
                                            ->helperText('Base liquidity for AMM calculations'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Payment & Withdrawal')
                            ->schema([
                                Forms\Components\Section::make('Payment Gateways')
                                    ->schema([
                                        Forms\Components\TextInput::make('paystack_public_key')
                                            ->label('Paystack Public Key')
                                            ->password()
                                            ->revealable(),
                                        Forms\Components\TextInput::make('paystack_secret_key')
                                            ->label('Paystack Secret Key')
                                            ->password()
                                            ->revealable(),
                                        Forms\Components\TextInput::make('flutterwave_public_key')
                                            ->label('Flutterwave Public Key')
                                            ->password()
                                            ->revealable(),
                                        Forms\Components\TextInput::make('flutterwave_secret_key')
                                            ->label('Flutterwave Secret Key')
                                            ->password()
                                            ->revealable(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Withdrawal Rules')
                                    ->schema([
                                        Forms\Components\TextInput::make('min_first_deposit')
                                            ->label('Minimum First Deposit (Kobo)')
                                            ->numeric()
                                            ->required()
                                            ->helperText('Minimum first deposit for new users'),
                                        Forms\Components\TextInput::make('min_withdrawal_returning')
                                            ->label('Minimum Withdrawal for Returning Users (Kobo)')
                                            ->numeric()
                                            ->required(),
                                        Forms\Components\TextInput::make('withdrawal_processing_time')
                                            ->label('Withdrawal Processing Time (Hours)')
                                            ->numeric()
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Referral System')
                            ->schema([
                                Forms\Components\Section::make('Referral Configuration')
                                    ->schema([
                                        Forms\Components\TextInput::make('default_referral_bonus')
                                            ->label('Default Referral Bonus (Kobo)')
                                            ->numeric()
                                            ->required()
                                            ->helperText('Default bonus amount for successful referrals'),
                                        Forms\Components\TextInput::make('referral_lock_period')
                                            ->label('Referral Lock Period (Days)')
                                            ->numeric()
                                            ->required()
                                            ->helperText('Days to lock referral bonus'),
                                        Forms\Components\TextInput::make('referral_bonus_percent')
                                            ->label('Referral Bonus Percentage')
                                            ->numeric()
                                            ->step(0.1)
                                            ->required()
                                            ->helperText('Percentage of referee activity to award as bonus'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Notifications')
                            ->schema([
                                Forms\Components\Section::make('Email Configuration')
                                    ->schema([
                                        Forms\Components\Select::make('mail_mailer')
                                            ->label('Mail Driver')
                                            ->options([
                                                'smtp' => 'SMTP',
                                                'sendmail' => 'Sendmail',
                                                'mailgun' => 'Mailgun',
                                                'ses' => 'Amazon SES',
                                                'log' => 'Log (Development)',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('mail_host')
                                            ->label('SMTP Host'),
                                        Forms\Components\TextInput::make('mail_port')
                                            ->label('SMTP Port')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('mail_username')
                                            ->label('SMTP Username'),
                                        Forms\Components\TextInput::make('mail_password')
                                            ->label('SMTP Password')
                                            ->password()
                                            ->revealable(),
                                        Forms\Components\TextInput::make('mail_from_address')
                                            ->label('From Email Address')
                                            ->email()
                                            ->required(),
                                        Forms\Components\TextInput::make('mail_from_name')
                                            ->label('From Name')
                                            ->required(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('SMS Configuration')
                                    ->schema([
                                        Forms\Components\Select::make('sms_provider')
                                            ->label('SMS Provider')
                                            ->options([
                                                'twilio' => 'Twilio',
                                                'nexmo' => 'Nexmo',
                                                'africastalking' => 'Africa\'s Talking',
                                            ]),
                                        Forms\Components\TextInput::make('twilio_sid')
                                            ->label('Twilio SID')
                                            ->password()
                                            ->revealable(),
                                        Forms\Components\TextInput::make('twilio_token')
                                            ->label('Twilio Auth Token')
                                            ->password()
                                            ->revealable(),
                                        Forms\Components\TextInput::make('twilio_from')
                                            ->label('Twilio From Number'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Security')
                            ->schema([
                                Forms\Components\Section::make('Security Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('rate_limit_per_minute')
                                            ->label('Rate Limit (Per Minute)')
                                            ->numeric()
                                            ->required(),
                                        Forms\Components\TextInput::make('password_min_length')
                                            ->label('Minimum Password Length')
                                            ->numeric()
                                            ->required(),
                                        Forms\Components\Toggle::make('password_require_uppercase')
                                            ->label('Require Uppercase Letters'),
                                        Forms\Components\Toggle::make('password_require_numbers')
                                            ->label('Require Numbers'),
                                        Forms\Components\Toggle::make('password_require_symbols')
                                            ->label('Require Symbols'),
                                        Forms\Components\Toggle::make('session_secure_cookie')
                                            ->label('Secure Cookies (HTTPS Only)'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Analytics')
                            ->schema([
                                Forms\Components\Section::make('Analytics & Monitoring')
                                    ->schema([
                                        Forms\Components\TextInput::make('google_analytics_id')
                                            ->label('Google Analytics ID'),
                                        Forms\Components\TextInput::make('facebook_pixel_id')
                                            ->label('Facebook Pixel ID'),
                                        Forms\Components\TextInput::make('sentry_dsn')
                                            ->label('Sentry DSN')
                                            ->password()
                                            ->revealable()
                                            ->helperText('For error monitoring'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        // Update .env file
        $this->updateEnvFile($data);
        
        // Clear config cache
        Artisan::call('config:clear');
        
        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getSettingsData(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'admin_email' => env('ADMIN_EMAIL'),
            'support_email' => env('SUPPORT_EMAIL'),
            'support_phone' => env('SUPPORT_PHONE'),
            'admin_name' => env('ADMIN_NAME'),
            'kyc_required' => env('KYC_REQUIRED', true),
            'referral_system_enabled' => env('REFERRAL_SYSTEM_ENABLED', true),
            'promo_codes_enabled' => env('PROMO_CODES_ENABLED', true),
            'two_factor_auth_enabled' => env('TWO_FACTOR_AUTH_ENABLED', true),
            'dark_mode_enabled' => env('DARK_MODE_ENABLED', true),
            'notifications_enabled' => env('NOTIFICATIONS_ENABLED', true),
            'min_stake_amount' => env('MIN_STAKE_AMOUNT', 100),
            'max_stake_amount' => env('MAX_STAKE_AMOUNT', 1000000),
            'protocol_fee_percent' => env('PROTOCOL_FEE_PERCENT', 2.0),
            'house_edge_percent' => env('HOUSE_EDGE_PERCENT', 2.0),
            'base_liquidity_pool' => env('BASE_LIQUIDITY_POOL', 1000000),
            'paystack_public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'paystack_secret_key' => env('PAYSTACK_SECRET_KEY'),
            'flutterwave_public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
            'flutterwave_secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
            'min_first_deposit' => env('MIN_FIRST_DEPOSIT', 500000),
            'min_withdrawal_returning' => env('MIN_WITHDRAWAL_RETURNING', 100000),
            'withdrawal_processing_time' => env('WITHDRAWAL_PROCESSING_TIME', 24),
            'default_referral_bonus' => env('DEFAULT_REFERRAL_BONUS', 50000),
            'referral_lock_period' => env('REFERRAL_LOCK_PERIOD', 30),
            'referral_bonus_percent' => env('REFERRAL_BONUS_PERCENT', 10.0),
            'mail_mailer' => env('MAIL_MAILER', 'log'),
            'mail_host' => env('MAIL_HOST'),
            'mail_port' => env('MAIL_PORT'),
            'mail_username' => env('MAIL_USERNAME'),
            'mail_password' => env('MAIL_PASSWORD'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS'),
            'mail_from_name' => env('MAIL_FROM_NAME'),
            'sms_provider' => env('SMS_PROVIDER'),
            'twilio_sid' => env('TWILIO_SID'),
            'twilio_token' => env('TWILIO_TOKEN'),
            'twilio_from' => env('TWILIO_FROM'),
            'rate_limit_per_minute' => env('RATE_LIMIT_PER_MINUTE', 60),
            'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
            'password_require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
            'password_require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
            'password_require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', true),
            'session_secure_cookie' => env('SESSION_SECURE_COOKIE', false),
            'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
            'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID'),
            'sentry_dsn' => env('SENTRY_LARAVEL_DSN'),
        ];
    }

    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            $envKey = strtoupper($key);
            $envValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            
            // Escape quotes in values
            if (is_string($envValue) && (str_contains($envValue, ' ') || str_contains($envValue, '#'))) {
                $envValue = '"' . str_replace('"', '\"', $envValue) . '"';
            }

            $pattern = "/^{$envKey}=.*/m";
            $replacement = "{$envKey}={$envValue}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envPath, $envContent);
    }
}
