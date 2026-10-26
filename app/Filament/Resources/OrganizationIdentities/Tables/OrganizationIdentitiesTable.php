<?php

namespace App\Filament\Resources\OrganizationIdentities\Tables;

use App\Filament\Actions\SafeForceDeleteAction;
use App\Models\OrganizationIdentity;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrganizationIdentitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(72)
                    ->wrap()
                    ->placeholder('Belum diisi'),
                TextColumn::make('images')
                    ->label('Gambar')
                    ->state(fn (OrganizationIdentity $record): string => count($record->images ?? []).' gambar'),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable(),
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
                // Data tunggal: tidak ada aksi massal.
            ]);
    }
}
