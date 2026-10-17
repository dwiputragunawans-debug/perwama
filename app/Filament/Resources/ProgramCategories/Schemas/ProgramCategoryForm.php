<?php

namespace App\Filament\Resources\ProgramCategories\Schemas;

use App\Models\ProgramCategory;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProgramCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan untuk dibuat otomatis.'),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->label('Ikon'),
                TextInput::make('color')
                    ->label('Warna'),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (ProgramCategory::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
