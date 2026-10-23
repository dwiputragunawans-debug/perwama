<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
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
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai'),
                TextInput::make('start_time')
                    ->label('Waktu Mulai'),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai'),
                TextInput::make('end_time')
                    ->label('Waktu Selesai'),
                TextInput::make('location')
                    ->label('Lokasi'),
                TextInput::make('poster')
                    ->label('Poster'),
                TextInput::make('speaker')
                    ->label('Pembicara'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'upcoming' => 'Akan Datang',
                        'ongoing' => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('upcoming')
                    ->required(),
                Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->required(),
                TextInput::make('views')
                    ->label('Jumlah Dilihat')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('published_at')->label('Tanggal Terbit')->default(now()),
            ]);
    }
}
