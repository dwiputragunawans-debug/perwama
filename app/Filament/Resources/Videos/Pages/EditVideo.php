<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Videos\VideoResource;
use Filament\Resources\Pages\EditRecord;

class EditVideo extends EditRecord
{
    protected static string $resource = VideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
