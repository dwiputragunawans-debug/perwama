<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\Actions\UploadGalleryPhotos;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGalleryItems extends ListRecords
{
    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Unggah massal: pilih album, lalu pilih beberapa foto sekaligus.
            UploadGalleryPhotos::make(),
            CreateAction::make(),
        ];
    }
}
