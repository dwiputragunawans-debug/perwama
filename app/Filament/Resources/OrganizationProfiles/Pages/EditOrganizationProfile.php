<?php

namespace App\Filament\Resources\OrganizationProfiles\Pages;

use App\Filament\Resources\OrganizationProfiles\OrganizationProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationProfile extends EditRecord
{
    protected static string $resource = OrganizationProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Profil adalah data tunggal: penghapusan tidak disediakan agar
            // halaman publik tidak kehilangan identitas organisasi.
        ];
    }
}
