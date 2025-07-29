<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StakeResource\Pages;
use App\Models\Stake;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StakeResource extends Resource
{
    protected static ?string $model = Stake::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Market Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stake Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('market_id')
                            ->relationship('market', 'question')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('side')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (Kobo)')
                            ->numeric()
                            ->required()
                            ->helperText('Stake amount in kobo (1 Naira = 100 kobo)'),
                        Forms\Components\TextInput::make('odds_at_placement')
                            ->label('Odds at Placement')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('potential_payout')
                            ->label('Potential Payout (Kobo)')
                            ->numeric()
                            ->helperText('Calculated potential payout in kobo'),
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
                Tables\Columns\TextColumn::make('market.question')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\BadgeColumn::make('side')
                    ->colors([
                        'success' => 'yes',
                        'danger' => 'no',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (₦)')
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('odds_at_placement')
                    ->label('Odds')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('potential_payout')
                    ->label('Potential Payout (₦)')
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('market.status')
                    ->label('Market Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'closed',
                        'primary' => 'settled',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\IconColumn::make('is_winning')
                    ->label('Won')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        if ($record->market->status !== 'settled') {
                            return null;
                        }
                        return $record->side === $record->market->winning_outcome;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('side')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No',
                    ]),
                Tables\Filters\SelectFilter::make('market_status')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value']) {
                            return $query->whereHas('market', function ($q) use ($data) {
                                $q->where('status', $data['value']);
                            });
                        }
                        return $query;
                    })
                    ->options([
                        'active' => 'Active Markets',
                        'closed' => 'Closed Markets',
                        'settled' => 'Settled Markets',
                        'cancelled' => 'Cancelled Markets',
                    ]),
                Tables\Filters\Filter::make('high_stakes')
                    ->query(fn (Builder $query): Builder => $query->where('amount', '>=', 1000000)) // 10,000 Naira
                    ->label('High Stakes (≥₦10,000)'),
                Tables\Filters\Filter::make('winning_stakes')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('market', function ($q) {
                            $q->where('status', 'settled');
                        })->whereRaw('side = (SELECT winning_outcome FROM markets WHERE markets.id = stakes.market_id)');
                    })
                    ->label('Winning Stakes'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStakes::route('/'),
            'create' => Pages\CreateStake::route('/create'),
            'view' => Pages\ViewStake::route('/{record}'),
            'edit' => Pages\EditStake::route('/{record}/edit'),
        ];
    }
}
