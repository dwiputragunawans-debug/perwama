<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name'),
                TextInput::make('action')
                    ->label('Aksi')
                    ->required(),
                TextInput::make('model_type')
                    ->label('Jenis Model'),
                TextInput::make('model_id')
                    ->label('ID Model')
                    ->numeric(),
                TextInput::make('old_values')
                    ->label('Nilai Lama'),
                TextInput::make('new_values')
                    ->label('Nilai Baru'),
                TextInput::make('ip_address')
                    ->label('Alamat IP'),
                TextInput::make('user_agent')
                    ->label('User Agent'),
            ]);
    }
}
