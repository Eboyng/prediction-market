<?php

namespace App\Filament\Resources\StakeResource\Pages;

use App\Filament\Resources\StakeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStakes extends ListRecords
{
    protected static string $resource = StakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
