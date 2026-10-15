<?php

namespace App\Filament\Resources\PanelSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PanelSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('brand_logo')
                    ->label('Logo')
                    ->disk('public')
                    ->height(40),
                ImageColumn::make('favicon')
                    ->label('Favicon')
                    ->disk('public')
                    ->height(24),
                TextColumn::make('brand_name')
                    ->label('Nama brand')
                    ->default('—'),
                TextColumn::make('brand_tagline')
                    ->label('Slogan')
                    ->limit(30)
                    ->default('—'),
                TextColumn::make('brand_logo_height')
                    ->label('Tinggi logo')
                    ->default('—'),
                ImageColumn::make('login_background')
                    ->label('Background login')
                    ->disk('public')
                    ->height(40),
                ColorColumn::make('login_background_color')
                    ->label('Warna dasar'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // Pengaturan tunggal: tidak ada aksi massal.
            ]);
    }
}
