<?php

namespace App\Filament\Resources\OrganizationIdentities\Pages;

use App\Filament\Resources\OrganizationIdentities\OrganizationIdentityResource;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationIdentities extends ListRecords
{
    protected static string $resource = OrganizationIdentityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Data tunggal: hanya ada satu record untuk diedit.
        ];
    }
}
