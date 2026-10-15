<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable(),
                TextColumn::make('organization.name')
                    ->label('Organisasi')
                    ->searchable(),
                TextColumn::make('region.name')
                    ->label('Wilayah')
                    ->searchable(),
                TextColumn::make('member_number')
                    ->label('Nomor Anggota')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
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
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('joined_at')
                    ->label('Tanggal Bergabung')
                    ->date()
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
