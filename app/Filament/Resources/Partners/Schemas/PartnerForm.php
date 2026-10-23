<?php

namespace App\Filament\Resources\Partners\Schemas;

use App\Filament\Forms\Components\RichEditor;
use App\Models\Partner;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('logo')
                    ->label('Logo'),
                TextInput::make('website')
                    ->label('Situs Web')
                    ->url(),
                TextInput::make('type')
                    ->label('Jenis'),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Partner::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
