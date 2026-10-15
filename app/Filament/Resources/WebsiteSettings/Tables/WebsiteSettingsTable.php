<?php

namespace App\Filament\Resources\WebsiteSettings\Tables;

use App\Models\WebsiteSetting;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('hero_images')
                    ->label('Pratinjau')
                    ->disk('public')
                    ->height(40)
                    ->state(fn (WebsiteSetting $record): ?string => $record->hero_images[0] ?? null),
                TextColumn::make('hero_images_count')
                    ->label('Jumlah gambar hero')
                    ->badge()
                    ->state(fn (WebsiteSetting $record): string => count($record->hero_images ?? []).' gambar'),
                TextColumn::make('region_section_title')
                    ->label('Judul bagian "Wilayah"')
                    ->limit(48)
                    ->placeholder('Memakai teks bawaan'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // Pengaturan tunggal: tidak ada aksi massal.
            ]);
    }
}
