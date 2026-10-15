<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('gallery_id')
                    ->label('Galeri')
                    ->relationship('gallery', 'title')
                    ->required(),
                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'photo' => 'Foto',
                        'video' => 'Video',
                    ])
                    ->default('photo')
                    ->required(),
                TextInput::make('file')
                    ->label('Berkas'),
                TextInput::make('video_url')
                    ->label('URL Video')
                    ->url(),
                TextInput::make('thumbnail')
                    ->label('Gambar Mini'),
                Textarea::make('caption')
                    ->label('Keterangan')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
