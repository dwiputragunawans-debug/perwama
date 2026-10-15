<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
                Select::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'title'),
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan untuk dibuat otomatis.'),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Select::make('album_category')
                    ->label('Kategori Album')
                    ->options([
                        'hut_perwama_ina' => 'HUT PERWAMA INA',
                        'pelatihan' => 'Pelatihan',
                        'bakti_sosial' => 'Bakti Sosial',
                        'seminar' => 'Seminar',
                        'rapat_organisasi' => 'Rapat Organisasi',
                        'kunjungan' => 'Kunjungan',
                        'kegiatan_daerah' => 'Kegiatan Daerah',
                        'budaya' => 'Budaya',
                    ])
                    ->required(),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/galleries')
                    ->imageEditor()
                    ->maxSize(10240),
                DatePicker::make('event_date')
                    ->label('Tanggal Acara'),
                Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
