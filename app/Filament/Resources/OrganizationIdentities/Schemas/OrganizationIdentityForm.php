<?php

namespace App\Filament\Resources\OrganizationIdentities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationIdentityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Organisasi')
                    ->description('Judul dan lirik pada bagian identitas organisasi di halaman Tentang Kami.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Mars PERWAMA INA'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(18)
                            ->columnSpanFull()
                            ->helperText('Tulis satu baris per lirik. Pisahkan bait dengan satu baris kosong. Baris pertama yang diawali "Ciptaan:" tampil sebagai kredit di bawah judul.'),
                    ]),

                Section::make('Gambar Pendukung')
                    ->description('Gambar yang tampil di bawah lirik, misalnya lembar partitur Mars PERWAMA INA. Boleh lebih dari satu gambar.')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Gambar')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->disk('public')
                            ->directory('uploads/organization-identities')
                            ->imageEditor()
                            ->imagePreviewHeight('224')
                            ->maxSize(10240)
                            ->maxFiles(20)
                            ->columnSpanFull()
                            ->helperText('JPG, PNG, atau WebP; maks. 10 MB per gambar. Seret gambar untuk mengatur urutan tampil. Klik gambar pada website untuk memperbesar.'),
                    ]),
            ]);
    }
}
