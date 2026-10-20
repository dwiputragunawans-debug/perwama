<?php

namespace App\Filament\Resources\OrganizationHistories\Schemas;

use App\Models\OrganizationHistory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrganizationHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year')
                    ->label('Tahun'),
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('location')
                    ->label('Lokasi')
                    ->maxLength(255)
                    ->helperText('Kota atau daerah berlangsungnya peristiwa, mis. Jakarta Selatan.'),
                FileUpload::make('image')
                    ->label('Gambar')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/organizationHistories')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (OrganizationHistory::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
