<?php

namespace App\Filament\Resources\StakeResource\Pages;

use App\Filament\Resources\StakeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStake extends EditRecord
{
    protected static string $resource = StakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
