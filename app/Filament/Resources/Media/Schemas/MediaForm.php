<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use App\Models\SeoMetadata;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('model_type')
                    ->label('Jenis Konten')
                    ->options(Media::SUPPORTED_MODELS)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('model_id', null)),
                Select::make('model_id')
                    ->label('Konten')
                    ->options(function (Get $get): array {
                        $modelType = $get('model_type');

                        if (! is_string($modelType) || ! isset(Media::SUPPORTED_MODELS[$modelType])) {
                            return [];
                        }

                        $column = SeoMetadata::labelColumnFor($modelType);

                        return $modelType::query()
                            ->orderBy($column)
                            ->limit(500)
                            ->pluck($column, 'id')
                            ->all();
                    })
                    ->searchable()
                    ->required(),
                FileUpload::make('path')
                    ->label('Berkas')
                    ->disk('public')
                    ->directory('uploads/media')
                    ->preserveFilenames()
                    ->maxSize(20480)
                    ->required()
                    ->helperText('Nama berkas, tipe MIME, dan ukuran terisi otomatis setelah disimpan.')
                    ->columnSpanFull(),
                TextInput::make('collection')
                    ->label('Koleksi')
                    ->default('lampiran')
                    ->helperText('Contoh: lampiran, dokumen, galeri.'),
                TextInput::make('name')
                    ->label('Nama Tampilan')
                    ->helperText('Kosongkan untuk memakai nama berkas.'),
                TextInput::make('alt')
                    ->label('Teks Alternatif')
                    ->helperText('Keterangan singkat isi berkas untuk pembaca layar.'),
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
