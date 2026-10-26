<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ActivityForm
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
                Select::make('event_id')
                    ->label('Agenda')
                    ->relationship('event', 'title'),
                Select::make('gallery_id')
                    ->label('Galeri')
                    ->relationship('gallery', 'title'),
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan untuk dibuat otomatis. Slug milik data terhapus tetap dianggap terpakai.'),
                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'kegiatan_sosial' => 'Kegiatan Sosial',
                        'pelatihan' => 'Pelatihan',
                        'seminar' => 'Seminar',
                        'organisasi' => 'Organisasi',
                        'lainnya' => 'Lainnya',
                    ])
                    ->default('lainnya')
                    ->required(),
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                DatePicker::make('activity_date')
                    ->label('Tanggal Kegiatan'),
                TextInput::make('location')
                    ->label('Lokasi'),
                TextInput::make('speaker')
                    ->label('Pembicara'),
                TextInput::make('participant_count')
                    ->label('Jumlah Peserta')
                    ->numeric(),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/activities')
                    ->imageEditor()
                    ->maxSize(10240),
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
