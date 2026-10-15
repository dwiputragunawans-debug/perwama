<?php

namespace App\Filament\Resources\SeoMetadata\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeoMetadataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('model_type')
                    ->label('Jenis Model')
                    ->searchable(),
                TextColumn::make('model_id')
                    ->label('ID Model')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->label('Judul Meta')
                    ->searchable(),
                TextColumn::make('meta_keywords')
                    ->label('Kata Kunci Meta')
                    ->searchable(),
                ImageColumn::make('og_image')
                    ->label('Gambar OG'),
                TextColumn::make('canonical_url')
                    ->label('URL Kanonik')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
