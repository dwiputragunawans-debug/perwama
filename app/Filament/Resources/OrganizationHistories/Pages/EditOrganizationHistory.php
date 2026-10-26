<?php

namespace App\Filament\Resources\OrganizationHistories\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\OrganizationHistories\OrganizationHistoryResource;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationHistory extends EditRecord
{
    protected static string $resource = OrganizationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
