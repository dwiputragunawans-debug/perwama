<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('model_type')
                    ->label('Jenis Model'),
                TextInput::make('model_id')
                    ->label('ID Model')
                    ->numeric(),
                TextInput::make('collection')
                    ->label('Koleksi'),
                TextInput::make('name')
                    ->label('Nama'),
                TextInput::make('file_name')
                    ->label('Nama Berkas'),
                TextInput::make('mime_type')
                    ->label('Tipe MIME'),
                TextInput::make('path')
                    ->label('Lokasi Berkas')
                    ->required(),
                TextInput::make('disk')
                    ->label('Penyimpanan')
                    ->required()
                    ->default('public'),
                TextInput::make('size')
                    ->label('Ukuran')
                    ->numeric(),
                TextInput::make('alt')
                    ->label('Teks Alternatif'),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Media::query()->max('sort_order') ?? 0) + 1),
            ]);
    }
}
