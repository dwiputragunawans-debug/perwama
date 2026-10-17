<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Models\GalleryItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->required()
                    // Urutan item dihitung per galeri: saat galeri dipilih,
                    // isi Urutan dengan nomor berikutnya pada galeri tersebut.
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (blank($state)) {
                            return;
                        }

                        $set('sort_order', (int) (GalleryItem::query()->where('gallery_id', $state)->max('sort_order') ?? 0) + 1);
                    }),
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
                    ->helperText('Angka kecil tampil lebih dulu. Otomatis diisi nomor berikutnya setelah galeri dipilih.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(1),
            ]);
    }
}
