<?php

namespace App\Filament\Resources\OrganizationHistories\Pages;

use App\Filament\Resources\OrganizationHistories\OrganizationHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationHistories extends ListRecords
{
    protected static string $resource = OrganizationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
