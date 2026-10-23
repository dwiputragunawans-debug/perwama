<?php

namespace App\Filament\Resources\OrganizationIdentities\Pages;

use App\Filament\Resources\OrganizationIdentities\OrganizationIdentityResource;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationIdentity extends EditRecord
{
    protected static string $resource = OrganizationIdentityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Data tunggal: tidak dapat dihapus.
        ];
    }
}
