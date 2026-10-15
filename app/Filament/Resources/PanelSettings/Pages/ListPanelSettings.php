<?php

namespace App\Filament\Resources\PanelSettings\Pages;

use App\Filament\Resources\PanelSettings\PanelSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListPanelSettings extends ListRecords
{
    protected static string $resource = PanelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pengaturan tunggal: hanya ada satu record untuk diedit.
        ];
    }
}
