<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketResource\Pages;
use App\Models\Market;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketResource extends Resource
{
    protected static ?string $model = Market::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Market Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Market Information')
                    ->schema([
                        Forms\Components\TextInput::make('question')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(Category::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\DateTimePicker::make('closes_at')
                            ->required()
                            ->minDate(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Market Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'closed' => 'Closed',
                                'settled' => 'Settled',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Forms\Components\Select::make('winning_outcome')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->visible(fn ($get) => in_array($get('status'), ['settled'])),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'closed',
                        'primary' => 'settled',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('total_stakes')
                    ->label('Total Stakes (₦)')
                    ->formatStateUsing(fn ($record) => number_format($record->stakes->sum('amount') / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('yes_stakes')
                    ->label('Yes Stakes')
                    ->formatStateUsing(fn ($record) => $record->stakes->where('side', 'yes')->count())
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_stakes')
                    ->label('No Stakes')
                    ->formatStateUsing(fn ($record) => $record->stakes->where('side', 'no')->count())
                    ->sortable(),
                Tables\Columns\TextColumn::make('closes_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'closed' => 'Closed',
                        'settled' => 'Settled',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('closes_soon')
                    ->query(fn (Builder $query): Builder => $query->where('closes_at', '<=', now()->addHours(24)))
                    ->label('Closes within 24 hours'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('settle')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'closed')
                    ->form([
                        Forms\Components\Select::make('winning_outcome')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'settled',
                            'winning_outcome' => $data['winning_outcome'],
                        ]);
                        
                        // Trigger settlement job
                        \App\Jobs\SettlementJob::dispatch($record);
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
            'index' => Pages\ListMarkets::route('/'),
            'create' => Pages\CreateMarket::route('/create'),
            'view' => Pages\ViewMarket::route('/{record}'),
            'edit' => Pages\EditMarket::route('/{record}/edit'),
        ];
    }
}
