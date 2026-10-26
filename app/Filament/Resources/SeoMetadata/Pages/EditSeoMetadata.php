<?php

namespace App\Filament\Resources\SeoMetadata\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\SeoMetadata\SeoMetadataResource;
use Filament\Resources\Pages\EditRecord;

class EditSeoMetadata extends EditRecord
{
    protected static string $resource = SeoMetadataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
