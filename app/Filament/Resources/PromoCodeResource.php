<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Promo Code Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->unique(PromoCode::class, 'code', ignoreRecord: true)
                            ->uppercase()
                            ->helperText('Unique promo code (will be converted to uppercase)'),
                        Forms\Components\TextInput::make('discount_percent')
                            ->label('Discount Percentage')
                            ->numeric()
                            ->step(0.1)
                            ->min(0)
                            ->max(100)
                            ->required()
                            ->suffix('%')
                            ->helperText('Percentage discount to apply'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->required()
                            ->minDate(now())
                            ->helperText('When this promo code expires'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Usage Limit')
                            ->numeric()
                            ->min(1)
                            ->helperText('Maximum number of times this code can be used (leave empty for unlimited)'),
                        Forms\Components\TextInput::make('used_count')
                            ->label('Times Used')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->helperText('Number of times this code has been used'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Whether this promo code is currently active'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->helperText('Internal description for this promo code'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Promo code copied!')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_stats')
                    ->label('Usage')
                    ->formatStateUsing(function ($record) {
                        $used = $record->used_count;
                        $limit = $record->usage_limit ?: '∞';
                        return "{$used} / {$limit}";
                    }),
                Tables\Columns\ProgressColumn::make('usage_progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        if (!$record->usage_limit) return 0;
                        return ($record->used_count / $record->usage_limit) * 100;
                    })
                    ->color(function ($state) {
                        if ($state >= 100) return 'danger';
                        if ($state >= 80) return 'warning';
                        return 'success';
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->expires_at->isPast()) return 'danger';
                        if ($record->expires_at->diffInDays() <= 7) return 'warning';
                        return 'success';
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(function ($record) {
                        if (!$record->is_active) return 'inactive';
                        if ($record->expires_at->isPast()) return 'expired';
                        if ($record->usage_limit && $record->used_count >= $record->usage_limit) return 'exhausted';
                        return 'active';
                    })
                    ->colors([
                        'success' => 'active',
                        'danger' => ['expired', 'exhausted'],
                        'secondary' => 'inactive',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Codes'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now()))
                    ->label('Expired Codes'),
                Tables\Filters\Filter::make('exhausted')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('used_count >= usage_limit AND usage_limit IS NOT NULL'))
                    ->label('Exhausted Codes'),
                Tables\Filters\Filter::make('unlimited')
                    ->query(fn (Builder $query): Builder => $query->whereNull('usage_limit'))
                    ->label('Unlimited Usage'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Toggle Promo Code Status')
                    ->modalDescription('Are you sure you want to change the status of this promo code?'),
                Tables\Actions\Action::make('reset_usage')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function ($record) {
                        $record->update(['used_count' => 0]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reset Usage Count')
                    ->modalDescription('Are you sure you want to reset the usage count for this promo code?')
                    ->visible(fn ($record) => $record->used_count > 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->requiresConfirmation(),
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
            'index' => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'view' => Pages\ViewPromoCode::route('/{record}'),
            'edit' => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
