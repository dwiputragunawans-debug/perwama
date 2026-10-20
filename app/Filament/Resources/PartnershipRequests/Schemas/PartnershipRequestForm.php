<?php

namespace App\Filament\Resources\PartnershipRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PartnershipRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('organization_name')
                    ->label('Nama Organisasi'),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email(),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel(),
                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'anggota' => 'Menjadi Anggota',
                        'relawan' => 'Menjadi Relawan',
                        'mitra' => 'Menjadi Mitra',
                        'kolaborasi' => 'Berkolaborasi',
                    ])
                    ->default('mitra')
                    ->required(),
                Textarea::make('message')
                    ->label('Pesan')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'processed' => 'Diproses',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('admin_notes')
                    ->label('Catatan Admin')
                    ->columnSpanFull(),
            ]);
    }
}
