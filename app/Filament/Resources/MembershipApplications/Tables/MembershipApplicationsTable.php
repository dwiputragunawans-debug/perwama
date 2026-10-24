<?php

namespace App\Filament\Resources\MembershipApplications\Tables;

use App\Models\MembershipApplication;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                TextColumn::make('nik')
                    ->label('NIK')
                    ->formatStateUsing(fn (MembershipApplication $record): string => $record->maskedNik() ?: '—'),
                TextColumn::make('birth_place')
                    ->label('Tempat Lahir')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable(),
                TextColumn::make('province')
                    ->label('Provinsi')
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable(),
                TextColumn::make('education')
                    ->label('Pendidikan')
                    ->searchable(),
                TextColumn::make('occupation')
                    ->label('Pekerjaan')
                    ->searchable(),
                TextColumn::make('interest_field')
                    ->label('Bidang Minat')
                    ->searchable(),
                TextColumn::make('maluku_origin')
                    ->label('Asal Daerah Maluku')
                    ->searchable(),
                TextColumn::make('domicile_region')
                    ->label('Wilayah Domisili')
                    ->searchable(),
                TextColumn::make('photo')
                    ->label('Foto')
                    ->searchable(),
                TextColumn::make('membership_type')
                    ->label('Jenis Keanggotaan')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('reviewed_at')
                    ->label('Waktu Ditinjau')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_by')
                    ->label('Ditinjau Oleh')
                    ->numeric()
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
