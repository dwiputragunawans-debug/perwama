<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use App\Filament\Actions\SafeDeleteBulkAction;
use App\Filament\Actions\SafeForceDeleteAction;
use App\Filament\Actions\SafeForceDeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
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
                // Setelah unggah massal, keterangan adalah pembeda utama antar
                // foto dalam satu album.
                TextColumn::make('caption')
                    ->label('Keterangan')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->searchable(),
                ImageColumn::make('file')
                    ->label('Berkas')
                    ->disk('public'),
                TextColumn::make('video_url')
                    ->label('URL Video')
                    ->searchable(),
                ImageColumn::make('thumbnail')
                    ->label('Gambar Mini')
                    ->disk('public'),
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn ($record): bool => $record->trashed()),
                RestoreAction::make(),
                SafeForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    SafeDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    SafeForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
