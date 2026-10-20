<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organization_id')
                    ->label('Organisasi')
                    ->relationship('organization', 'name'),
                Select::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan untuk dibuat otomatis.'),
                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'ranting' => 'Ranting',
                        'cabang' => 'Cabang',
                        'wilayah' => 'Wilayah',
                    ])
                    ->default('ranting')
                    ->required(),
                TextInput::make('leader_name')
                    ->label('Nama Ketua'),
                Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel(),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email(),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('member_count')
                    ->label('Jumlah Anggota')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
