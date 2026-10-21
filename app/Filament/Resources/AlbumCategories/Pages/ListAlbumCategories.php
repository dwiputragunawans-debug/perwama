<?php

namespace App\Filament\Resources\AlbumCategories\Pages;

use App\Filament\Resources\AlbumCategories\AlbumCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlbumCategories extends ListRecords
{
    protected static string $resource = AlbumCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
