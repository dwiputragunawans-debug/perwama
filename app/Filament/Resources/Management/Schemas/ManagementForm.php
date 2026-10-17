<?php

namespace App\Filament\Resources\Management\Schemas;

use App\Models\Management;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ManagementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('region_id')
                    ->relationship('region', 'name')
                    ->label('Wilayah'),
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->label('Organisasi'),
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('position')
                    ->label('Jabatan')
                    ->required(),
                Select::make('level')
                    ->label('Tingkat')
                    ->options([
                        'dpp' => 'Dewan Pimpinan Pusat (DPP)',
                        'dpw' => 'Dewan Pimpinan Wilayah (DPW)',
                        'pc' => 'Pimpinan Cabang (PC)',
                    ])
                    ->default('dpp')
                    ->required(),
                TextInput::make('period')
                    ->label('Periode'),
                FileUpload::make('photo')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/managements')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('email')
                    ->label('Email')
                    ->email(),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel(),
                Textarea::make('bio')
                    ->label('Profil Singkat')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Management::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
