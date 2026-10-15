<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
                Select::make('parent_id')
                    ->label('Organisasi Induk')
                    ->relationship('parent', 'name'),
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
                        'dpp' => 'Dewan Pimpinan Pusat (DPP)',
                        'dpw' => 'Dewan Pimpinan Wilayah (DPW)',
                        'pc' => 'Pimpinan Cabang (PC)',
                    ])
                    ->default('pc')
                    ->required(),
                TextInput::make('leader_name')
                    ->label('Nama Ketua'),
                TextInput::make('secretary_name')
                    ->label('Nama Sekretaris'),
                Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel(),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email(),
                TextInput::make('whatsapp')
                    ->label('WhatsApp'),
                TextInput::make('facebook')
                    ->label('Facebook'),
                TextInput::make('instagram')
                    ->label('Instagram'),
                TextInput::make('youtube')
                    ->label('YouTube'),
                TextInput::make('logo')
                    ->label('Logo'),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/organizations')
                    ->imageEditor()
                    ->maxSize(10240),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                DatePicker::make('founded_at')
                    ->label('Tanggal Berdiri'),
                TextInput::make('member_count')
                    ->label('Jumlah Anggota')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
