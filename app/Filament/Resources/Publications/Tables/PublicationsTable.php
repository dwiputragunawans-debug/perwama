<?php

namespace App\Filament\Resources\Publications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.title')
                    ->label('Program')
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
                TextColumn::make('file')
                    ->label('Berkas')
                    ->searchable(),
                ImageColumn::make('cover_image')
                    ->label('Gambar Sampul')
                    ->disk('public'),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->searchable(),
                TextColumn::make('page_count')
                    ->label('Jumlah Halaman')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('file_size')
                    ->label('Ukuran Berkas')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('download_count')
                    ->label('Jumlah Unduhan')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
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
