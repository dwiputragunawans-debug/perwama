<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Events\EventResource;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
