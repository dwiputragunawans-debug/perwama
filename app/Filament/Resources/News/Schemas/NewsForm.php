<?php

namespace App\Filament\Resources\News\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'title'),
                Select::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan untuk dibuat otomatis.'),
                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'berita' => 'Berita',
                        'artikel' => 'Artikel',
                        'opini' => 'Opini',
                        'pengumuman' => 'Pengumuman',
                    ])
                    ->default('berita')
                    ->required(),
                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'organisasi' => 'Organisasi',
                        'kegiatan' => 'Kegiatan',
                        'sosial' => 'Sosial',
                        'pendidikan' => 'Pendidikan',
                        'perempuan' => 'Perempuan',
                        'ekonomi' => 'Ekonomi',
                        'hukum_ham' => 'Hukum & HAM',
                        'budaya' => 'Budaya',
                        'pengumuman' => 'Pengumuman',
                    ])
                    ->required(),
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('Konten')
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/news')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('author')
                    ->label('Penulis'),
                TextInput::make('source')
                    ->label('Sumber'),
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
