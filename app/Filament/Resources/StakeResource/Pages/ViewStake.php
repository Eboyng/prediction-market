<?php

namespace App\Filament\Resources\StakeResource\Pages;

use App\Filament\Resources\StakeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStake extends ViewRecord
{
    protected static string $resource = StakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
