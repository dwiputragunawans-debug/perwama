<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Galleries\GalleryResource;
use App\Filament\Resources\GalleryItems\Actions\UploadGalleryPhotos;
use App\Models\Gallery;
use Filament\Resources\Pages\EditRecord;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Gallery $gallery */
        $gallery = $this->getRecord();

        return [
            UploadGalleryPhotos::make($gallery),
            SafeDeleteAction::make(),
        ];
    }
}
