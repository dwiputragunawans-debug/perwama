<?php

namespace App\Filament\Resources\OrganizationFigures\Schemas;

use App\Models\OrganizationFigure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrganizationFigureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Tokoh')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nama lengkap pendiri'),
                TextInput::make('position')
                    ->label('Jabatan / Peran')
                    ->required()
                    ->maxLength(255)
                    ->default('Pendiri')
                    ->helperText('Teks ini tampil di bawah nama, misalnya "Pendiri".'),
                FileUpload::make('photo')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/organization-figures')
                    ->imageEditor()
                    ->imagePreviewHeight('224')
                    ->maxSize(5120)
                    ->helperText('JPG, PNG, atau WebP; maks. 5 MB. Foto persegi paling sesuai karena tampil bulat pada kartu. Bila kosong, kartu menampilkan inisial nama.'),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (OrganizationFigure::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Hanya tokoh aktif yang tampil pada halaman Tentang Kami.')
                    ->default(true)
                    ->required(),
            ]);
    }
}
