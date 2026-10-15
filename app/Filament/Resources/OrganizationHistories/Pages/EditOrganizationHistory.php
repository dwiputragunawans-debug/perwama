<?php

namespace App\Filament\Resources\OrganizationHistories\Pages;

use App\Filament\Resources\OrganizationHistories\OrganizationHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationHistory extends EditRecord
{
    protected static string $resource = OrganizationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
