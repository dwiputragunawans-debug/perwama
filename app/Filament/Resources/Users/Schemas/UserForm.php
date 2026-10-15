<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Biarkan kosong bila tidak ingin mengubah kata sandi.'),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(30),
                FileUpload::make('photo')
                    ->label('Foto')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('uploads/users')
                    ->imageEditor()
                    ->maxSize(5120),
                Toggle::make('is_admin')
                    ->label('Akses Admin')
                    ->helperText(fn (?User $record): string => ($record?->is(auth()->user()) ?? false)
                        ? 'Akses admin akun sendiri tidak dapat dicabut agar Anda tidak terkunci dari panel.'
                        : 'Pengguna dengan akses admin dapat mengelola seluruh konten website. Jumlah admin tidak dibatasi.')
                    ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false)
                    ->default(true),
            ]);
    }
}
