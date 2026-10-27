<?php

namespace App\Filament\Resources\Organizations\Tables;

use App\Filament\Actions\SafeDeleteBulkAction;
use App\Filament\Actions\SafeForceDeleteAction;
use App\Filament\Actions\SafeForceDeleteBulkAction;
use App\Models\Organization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('region.name')
                    ->label('Wilayah')
                    ->searchable(),
                TextColumn::make('parent.name')
                    ->label('Organisasi Induk')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (?string $state): string => Organization::TYPES[$state] ?? (string) $state)
                    ->searchable(),
                TextColumn::make('leader_name')
                    ->label('Nama Ketua')
                    ->searchable(),
                TextColumn::make('secretary_name')
                    ->label('Nama Sekretaris')
                    ->searchable(),
                TextColumn::make('logo')
                    ->label('Logo')
                    ->searchable(),
                TextColumn::make('member_count')
                    ->label('Jumlah Anggota')
                    ->numeric()
                    ->sortable(),
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
