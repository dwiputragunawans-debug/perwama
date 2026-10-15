<?php

namespace App\Filament\Resources\MembershipApplications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MembershipApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('nik')
                    ->label('NIK'),
                TextInput::make('birth_place')
                    ->label('Tempat Lahir'),
                DatePicker::make('birth_date')
                    ->label('Tanggal Lahir'),
                Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Perempuan' => 'Perempuan',
                        'Laki-laki' => 'Laki-laki',
                    ])
                    ->required(),
                Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->label('Kota'),
                TextInput::make('province')
                    ->label('Provinsi'),
                TextInput::make('whatsapp')
                    ->label('WhatsApp'),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email(),
                TextInput::make('education')
                    ->label('Pendidikan'),
                TextInput::make('occupation')
                    ->label('Pekerjaan'),
                TextInput::make('interest_field')
                    ->label('Bidang Minat'),
                TextInput::make('maluku_origin')
                    ->label('Asal Daerah Maluku'),
                TextInput::make('domicile_region')
                    ->label('Wilayah Domisili'),
                TextInput::make('photo')
                    ->label('Foto'),
                Select::make('membership_type')
                    ->label('Jenis Keanggotaan')
                    ->options([
                        'anggota' => 'Anggota',
                        'relawan' => 'Relawan',
                        'mitra' => 'Mitra',
                        'kolaborasi' => 'Kolaborasi',
                    ])
                    ->default('anggota')
                    ->required(),
                Textarea::make('motivation')
                    ->label('Motivasi')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('admin_notes')
                    ->label('Catatan Admin')
                    ->columnSpanFull(),
                DateTimePicker::make('reviewed_at')
                    ->label('Waktu Ditinjau'),
                Select::make('reviewed_by')
                    ->label('Ditinjau Oleh')
                    ->relationship('reviewer', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
