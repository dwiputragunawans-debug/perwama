<?php

namespace App\Filament\Resources\Management\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ManagementTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'dpp' => 'DPP',
                        'dpw' => 'DPW',
                        'pc' => 'PC',
                        default => '—',
                    })
                    ->sortable(),
                TextColumn::make('region.name')
                    ->label('Wilayah')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('period')
                    ->label('Periode')
                    ->searchable(),
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
                SelectFilter::make('level')
                    ->label('Tingkat')
                    ->options([
                        'dpp' => 'Dewan Pimpinan Pusat (DPP)',
                        'dpw' => 'Dewan Pimpinan Wilayah (DPW)',
                        'pc' => 'Pimpinan Cabang (PC)',
                    ]),
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
