<?php

namespace App\Filament\Resources\AlbumCategories\Pages;

use App\Filament\Resources\AlbumCategories\AlbumCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlbumCategory extends CreateRecord
{
    protected static string $resource = AlbumCategoryResource::class;
}
