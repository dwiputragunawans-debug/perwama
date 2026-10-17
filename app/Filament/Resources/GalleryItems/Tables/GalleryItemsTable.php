<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('gallery.title')
                    ->label('Galeri')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->searchable(),
                TextColumn::make('file')
                    ->label('Berkas')
                    ->searchable(),
                TextColumn::make('video_url')
                    ->label('URL Video')
                    ->searchable(),
                TextColumn::make('thumbnail')
                    ->label('Gambar Mini')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
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
                // Urutan item hanya berlaku di dalam satu galeri, jadi daftar
                // perlu dapat disaring per galeri agar susunannya terbaca.
                SelectFilter::make('gallery_id')
                    ->label('Galeri')
                    ->relationship('gallery', 'title')
                    ->searchable()
                    ->preload(),
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
