<?php

namespace App\Filament\Resources\Galleries\Tables;

use App\Filament\Actions\SafeDeleteBulkAction;
use App\Filament\Actions\SafeForceDeleteAction;
use App\Filament\Actions\SafeForceDeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GalleriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('region.name')
                    ->label('Wilayah')
                    ->searchable(),
                TextColumn::make('program.title')
                    ->label('Program')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('albumCategory.name')
                    ->label('Kategori Album')
                    ->searchable()
                    ->placeholder('Belum dikategorikan'),
                ImageColumn::make('cover_image')
                    ->label('Gambar Sampul')
                    ->disk('public'),
                TextColumn::make('event_date')
                    ->label('Tanggal Acara')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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
