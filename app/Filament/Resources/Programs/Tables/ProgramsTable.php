<?php

namespace App\Filament\Resources\Programs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('program_category_id')
                    ->label('Kategori Program')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('region.name')
                    ->label('Wilayah')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->searchable(),
                ImageColumn::make('cover_image')
                    ->label('Gambar Sampul')
                    ->disk('public'),
                TextColumn::make('document')
                    ->label('Dokumen')
                    ->searchable(),
                TextColumn::make('budget')
                    ->label('Anggaran')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fiscal_year')
                    ->label('Tahun Anggaran')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('views')
                    ->label('Jumlah Dilihat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->label('Judul Meta')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->dateTime()
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
