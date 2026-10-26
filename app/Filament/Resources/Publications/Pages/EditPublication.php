<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Publications\PublicationResource;
use Filament\Resources\Pages\EditRecord;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
