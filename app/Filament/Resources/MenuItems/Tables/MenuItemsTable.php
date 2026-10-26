<?php

namespace App\Filament\Resources\MenuItems\Tables;

use App\Filament\Actions\SafeDeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label('Nama menu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('route_name')
                    ->label('Tujuan')
                    ->badge()
                    ->searchable(),
                TextColumn::make('pattern')
                    ->label('Pola aktif')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Tampil'),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    SafeDeleteBulkAction::make(),
                ]),
            ]);
    }
}
