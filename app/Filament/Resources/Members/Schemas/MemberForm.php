<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name'),
                Select::make('organization_id')
                    ->label('Organisasi')
                    ->relationship('organization', 'name'),
                Select::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
                TextInput::make('member_number')
                    ->label('Nomor Anggota')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('full_name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('nik')
                    ->label('NIK')
                    ->password()
                    ->revealable()
                    ->maxLength(32)
                    ->helperText('NIK disimpan terenkripsi. Gunakan ikon mata untuk melihat atau mengubahnya.'),
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
                Select::make('status')
                    ->label('Status')
                    ->options(Member::STATUSES)
                    ->default('pending')
                    ->required(),
                DatePicker::make('joined_at')
                    ->label('Tanggal Bergabung'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
