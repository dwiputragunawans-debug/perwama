<?php

namespace App\Filament\Resources\Videos\Schemas;

use App\Models\Video;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan untuk dibuat otomatis.'),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('URL')
                    ->url(),
                TextInput::make('thumbnail')
                    ->label('Gambar Mini'),
                Select::make('category')
                    ->label('Kategori')
                    ->options(Video::CATEGORIES)
                    ->searchable(),
                Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Video::query()->max('sort_order') ?? 0) + 1),
                DateTimePicker::make('published_at')->label('Tanggal Terbit')->default(now()),
            ]);
    }
}
