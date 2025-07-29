<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Wallet Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('balance')
                            ->label('Balance (Kobo)')
                            ->numeric()
                            ->required()
                            ->helperText('Balance in kobo (1 Naira = 100 kobo)'),
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
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance (₦)')
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_deposits')
                    ->label('Total Deposits (₦)')
                    ->formatStateUsing(function ($record) {
                        $deposits = $record->user->activityLogs()
                            ->where('action', 'deposit')
                            ->sum('metadata->amount');
                        return number_format($deposits / 100, 2);
                    }),
                Tables\Columns\TextColumn::make('total_withdrawals')
                    ->label('Total Withdrawals (₦)')
                    ->formatStateUsing(function ($record) {
                        $withdrawals = $record->user->activityLogs()
                            ->where('action', 'withdrawal')
                            ->sum('metadata->amount');
                        return number_format($withdrawals / 100, 2);
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_balance')
                    ->query(fn (Builder $query): Builder => $query->where('balance', '>', 0))
                    ->label('Has Balance'),
                Tables\Filters\Filter::make('high_balance')
                    ->query(fn (Builder $query): Builder => $query->where('balance', '>=', 10000000)) // 100,000 Naira
                    ->label('High Balance (≥₦100,000)'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('adjust_balance')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'credit' => 'Credit (Add Funds)',
                                'debit' => 'Debit (Remove Funds)',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (Kobo)')
                            ->numeric()
                            ->required()
                            ->helperText('Amount in kobo (1 Naira = 100 kobo)'),
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->helperText('Reason for balance adjustment'),
                    ])
                    ->action(function ($record, array $data) {
                        $amount = $data['amount'];
                        if ($data['type'] === 'debit') {
                            $amount = -$amount;
                        }
                        
                        $record->increment('balance', $amount);
                        
                        // Log the adjustment
                        $record->user->logActivity('balance_adjustment', [
                            'type' => $data['type'],
                            'amount' => abs($amount),
                            'reason' => $data['reason'],
                            'admin_id' => auth()->id(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'view' => Pages\ViewWallet::route('/{record}'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}
