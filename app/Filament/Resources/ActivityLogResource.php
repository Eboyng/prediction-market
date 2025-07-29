<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Activity Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Activity Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('action')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('action')
                    ->colors([
                        'success' => ['deposit', 'stake_placed', 'referral_bonus'],
                        'warning' => ['withdrawal', 'kyc_submitted'],
                        'danger' => ['withdrawal_failed', 'kyc_rejected'],
                        'primary' => ['login', 'logout', 'profile_updated'],
                        'secondary' => ['market_created', 'promo_redeemed'],
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (₦)')
                    ->formatStateUsing(function ($record) {
                        $amount = $record->metadata['amount'] ?? null;
                        return $amount ? number_format($amount / 100, 2) : '-';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->formatStateUsing(function ($record) {
                        return $record->metadata['ip_address'] ?? '-';
                    }),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Device')
                    ->formatStateUsing(function ($record) {
                        $userAgent = $record->metadata['user_agent'] ?? '';
                        if (str_contains($userAgent, 'Mobile')) {
                            return 'Mobile';
                        } elseif (str_contains($userAgent, 'Tablet')) {
                            return 'Tablet';
                        } else {
                            return 'Desktop';
                        }
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal',
                        'stake_placed' => 'Stake Placed',
                        'kyc_submitted' => 'KYC Submitted',
                        'kyc_approved' => 'KYC Approved',
                        'kyc_rejected' => 'KYC Rejected',
                        'referral_bonus' => 'Referral Bonus',
                        'promo_redeemed' => 'Promo Redeemed',
                        'market_created' => 'Market Created',
                        'balance_adjustment' => 'Balance Adjustment',
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('financial_activities')
                    ->query(fn (Builder $query): Builder => $query->whereIn('action', ['deposit', 'withdrawal', 'stake_placed', 'referral_bonus']))
                    ->label('Financial Activities'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('view_metadata')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Activity Metadata')
                    ->modalContent(function ($record) {
                        return view('filament.modals.activity-metadata', ['metadata' => $record->metadata]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Activity logs should not be manually created
    }

    public static function canEdit($record): bool
    {
        return false; // Activity logs should not be edited
    }
}
