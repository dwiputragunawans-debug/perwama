<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
