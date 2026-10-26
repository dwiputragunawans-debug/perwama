<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\GalleryItems\Actions\UploadGalleryPhotos;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Models\GalleryItem;
use Filament\Resources\Pages\EditRecord;

class EditGalleryItem extends EditRecord
{
    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        /** @var GalleryItem $item */
        $item = $this->getRecord();

        return [
            // Menambah banyak foto ke album yang sama tanpa harus membuat
            // item baru satu per satu.
            UploadGalleryPhotos::make($item->gallery),
            SafeDeleteAction::make(),
        ];
    }
}
