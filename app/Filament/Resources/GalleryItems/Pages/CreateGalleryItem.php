<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\Actions\UploadGalleryPhotos;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryItem extends CreateRecord
{
    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        // Admin dapat langsung mengunggah banyak foto tanpa harus menyimpan
        // formulir item terlebih dahulu.
        return [
            UploadGalleryPhotos::make(),
        ];
    }
}
