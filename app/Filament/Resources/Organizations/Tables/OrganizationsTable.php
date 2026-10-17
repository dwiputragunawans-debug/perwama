<?php

namespace App\Filament\Resources\Organizations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->searchable(),
                TextColumn::make('leader_name')
                    ->label('Nama Ketua')
                    ->searchable(),
                TextColumn::make('secretary_name')
                    ->label('Nama Sekretaris')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable(),
                TextColumn::make('facebook')
                    ->label('Facebook')
                    ->searchable(),
                TextColumn::make('instagram')
                    ->label('Instagram')
                    ->searchable(),
                TextColumn::make('youtube')
                    ->label('YouTube')
                    ->searchable(),
                TextColumn::make('logo')
                    ->label('Logo')
                    ->searchable(),
                ImageColumn::make('cover_image')
                    ->label('Gambar Sampul')
                    ->disk('public'),
                TextColumn::make('founded_at')
                    ->label('Tanggal Berdiri')
                    ->date()
                    ->sortable(),
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
