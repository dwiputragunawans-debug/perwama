<?php

namespace App\Filament\Resources\Management\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Management\ManagementResource;
use Filament\Resources\Pages\EditRecord;

class EditManagement extends EditRecord
{
    protected static string $resource = ManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
