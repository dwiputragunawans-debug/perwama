<?php

namespace App\Filament\Resources\Publications\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PublicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                        'laporan' => 'Laporan',
                        'buku' => 'Buku',
                        'modul' => 'Modul',
                        'materi_pendidikan' => 'Materi Pendidikan',
                        'kajian' => 'Kajian',
                        'dokumentasi' => 'Dokumentasi',
                        'panduan' => 'Panduan',
                    ])
                    ->required(),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('file')
                    ->label('Berkas'),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/publications')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('year')
                    ->label('Tahun'),
                TextInput::make('page_count')
                    ->label('Jumlah Halaman')
                    ->numeric(),
                TextInput::make('file_size')
                    ->label('Ukuran Berkas')
                    ->numeric(),
                TextInput::make('download_count')
                    ->label('Jumlah Unduhan')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->required(),
                DateTimePicker::make('published_at')->label('Tanggal Terbit')->default(now()),
            ]);
    }
}
