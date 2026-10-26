<?php

namespace App\Filament\Resources\Stories\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Stories\StoryResource;
use Filament\Resources\Pages\EditRecord;

class EditStory extends EditRecord
{
    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
