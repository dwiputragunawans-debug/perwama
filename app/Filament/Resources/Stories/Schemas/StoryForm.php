<?php

namespace App\Filament\Resources\Stories\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
                Select::make('organization_id')
                    ->label('Organisasi')
                    ->relationship('organization', 'name'),
                Select::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'title'),
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan untuk dibuat otomatis.'),
                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'kisah_inspiratif' => 'Kisah Inspiratif',
                        'tokoh_perempuan' => 'Tokoh Perempuan',
                        'perempuan_maluku' => 'Perempuan Maluku',
                        'cerita_komunitas' => 'Cerita Komunitas',
                    ])
                    ->default('kisah_inspiratif')
                    ->required(),
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('Konten')
                    ->columnSpanFull(),
                TextInput::make('person_name')
                    ->label('Nama Tokoh'),
                TextInput::make('person_photo')
                    ->label('Foto Tokoh'),
                TextInput::make('person_age')
                    ->label('Usia Tokoh')
                    ->numeric(),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/stories')
                    ->imageEditor()
                    ->maxSize(10240),
                Textarea::make('quote')
                    ->label('Kutipan')
                    ->columnSpanFull(),
                RichEditor::make('journey')
                    ->label('Perjalanan')
                    ->columnSpanFull(),
                RichEditor::make('activities')
                    ->label('Aktivitas')
                    ->columnSpanFull(),
                RichEditor::make('impact')
                    ->label('Dampak')
                    ->columnSpanFull(),
                Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->required(),
                TextInput::make('views')
                    ->label('Jumlah Dilihat')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('meta_title')
                    ->label('Judul Meta'),
                Textarea::make('meta_description')
                    ->label('Deskripsi Meta')
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')->label('Tanggal Terbit')->default(now()),
            ]);
    }
}
