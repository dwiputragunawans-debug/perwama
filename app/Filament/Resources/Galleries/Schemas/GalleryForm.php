<?php

namespace App\Filament\Resources\Galleries\Schemas;

use App\Filament\Forms\Components\RichEditor;
use App\Models\Gallery;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
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
                    ->helperText('Kosongkan untuk dibuat otomatis. Slug milik data terhapus tetap dianggap terpakai.'),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Select::make('album_category_id')
                    ->label('Kategori Album')
                    ->relationship(
                        'albumCategory',
                        'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->ordered(),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilihan kategori dikelola melalui menu Galeri → Kategori Album.'),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/galleries')
                    ->imageEditor()
                    ->maxSize(10240),
                DatePicker::make('event_date')
                    ->label('Tanggal Acara'),
                Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Gallery::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
