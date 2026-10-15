<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('region.name')
                    ->label('Wilayah')
                    ->searchable(),
                TextColumn::make('organization.name')
                    ->label('Organisasi')
                    ->searchable(),
                TextColumn::make('program.title')
                    ->label('Program')
                    ->searchable(),
                TextColumn::make('event.title')
                    ->label('Agenda')
                    ->searchable(),
                TextColumn::make('gallery.title')
                    ->label('Galeri')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('activity_date')
                    ->label('Tanggal Kegiatan')
                    ->date()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),
                TextColumn::make('speaker')
                    ->label('Pembicara')
                    ->searchable(),
                TextColumn::make('participant_count')
                    ->label('Jumlah Peserta')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('cover_image')
                    ->label('Gambar Sampul'),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('views')
                    ->label('Jumlah Dilihat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->label('Judul Meta')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->dateTime()
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
