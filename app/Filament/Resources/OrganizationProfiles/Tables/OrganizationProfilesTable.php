<?php

namespace App\Filament\Resources\OrganizationProfiles\Tables;

use App\Filament\Actions\SafeForceDeleteAction;
use App\Filament\Actions\SafeForceDeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrganizationProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('abbreviation')
                    ->label('Singkatan')
                    ->searchable(),
                TextColumn::make('tagline')
                    ->label('Tagline')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable(),
                TextColumn::make('province')
                    ->label('Provinsi')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->label('Kode Pos')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable(),
                TextColumn::make('founded_year')
                    ->label('Tahun Berdiri')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('facebook')
                    ->label('Facebook')
                    ->searchable(),
                TextColumn::make('instagram')
                    ->label('Instagram')
                    ->searchable(),
                TextColumn::make('youtube')
                    ->label('YouTube')
                    ->searchable(),
                TextColumn::make('twitter')
                    ->label('Twitter')
                    ->searchable(),
                TextColumn::make('tiktok')
                    ->label('TikTok')
                    ->searchable(),
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
                    // Profil adalah data tunggal: tidak ada aksi hapus.
                    RestoreBulkAction::make(),
                    SafeForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
