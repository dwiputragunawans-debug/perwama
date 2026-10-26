<?php

namespace App\Filament\Resources\AlbumCategories\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\AlbumCategories\AlbumCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditAlbumCategory extends EditRecord
{
    protected static string $resource = AlbumCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
