<?php

namespace App\Filament\Resources\OrganizationIdentities\Schemas;

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
            ]);
    }
}
