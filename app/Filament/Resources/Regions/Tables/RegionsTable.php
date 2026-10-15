<?php

namespace App\Filament\Resources\Regions\Tables;

use App\Models\Region;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent.name')
                    ->label('Wilayah Induk')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (?string $state): string => Region::TYPES[$state] ?? (string) $state)
                    ->searchable(),
                TextColumn::make('province')
                    ->label('Provinsi')
                    ->searchable(),
                TextColumn::make('island')
                    ->label('Pulau')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->label('Lintang')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->label('Bujur')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('cover_image')
                    ->label('Gambar Sampul'),
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
