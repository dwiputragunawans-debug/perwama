<?php

namespace App\Filament\Resources\PanelSettings\Pages;

use App\Filament\Resources\PanelSettings\PanelSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditPanelSetting extends EditRecord
{
    protected static string $resource = PanelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pengaturan tunggal: tidak dapat dihapus.
        ];
    }
}
