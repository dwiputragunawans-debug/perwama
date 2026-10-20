<?php

namespace App\Filament\Resources\WebsiteSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WebsiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gambar Hero Beranda')
                    ->description('Foto untuk carousel latar penuh pada bagian hero di halaman beranda website.')
                    ->schema([
                        FileUpload::make('hero_images')
                            ->label('Gambar hero')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->disk('public')
                            ->directory('uploads/hero')
                            ->maxSize(10240)
                            ->helperText('Disarankan gambar lanskap (mis. 1920x1080). Urutan dapat diatur dengan menyeret gambar. Bila kosong, cover galeri dipakai sebagai cadangan.'),
                    ]),

                Section::make('Bagian "Wilayah"')
                    ->description('Teks pada bagian sebaran wilayah di halaman beranda. Bila dikosongkan, teks bawaan situs yang dipakai.')
                    ->schema([
                        TextInput::make('region_section_title')
                            ->label('Judul')
                            ->maxLength(255)
                            ->placeholder('Jejaring perempuan Maluku di seluruh Nusantara'),
                        Textarea::make('region_section_description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Dari Maluku hingga perantauan, PERWAMA INA hadir melalui wilayah, cabang, dan komunitas anggota yang tersebar di berbagai provinsi.'),
                    ]),
            ]);
    }
}
