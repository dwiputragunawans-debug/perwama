<?php

namespace App\Filament\Resources\Regions\Schemas;

use App\Models\Region;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('Wilayah Induk')
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
                    ->options(Region::TYPES)
                    ->default('province')
                    ->required(),
                TextInput::make('province')
                    ->label('Provinsi'),
                TextInput::make('island')
                    ->label('Pulau'),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->label('Lintang')
                    ->numeric(),
                TextInput::make('longitude')
                    ->label('Bujur')
                    ->numeric(),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/regions')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Region::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
