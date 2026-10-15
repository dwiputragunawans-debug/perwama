<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email(),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel(),
                TextInput::make('subject')
                    ->label('Subjek'),
                Textarea::make('message')
                    ->label('Pesan')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_read')
                    ->label('Sudah Dibaca')
                    ->required(),
                DateTimePicker::make('read_at')
                    ->label('Waktu Dibaca'),
                TextInput::make('ip_address')
                    ->label('Alamat IP'),
            ]);
    }
}
