<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Models\Program;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('program_category_id')
                    ->label('Kategori Program')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
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
                        'kerja_anggaran' => 'Program Kerja & Anggaran',
                        'lainnya' => 'Program Lainnya',
                    ])
                    ->default('lainnya')
                    ->required(),
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Textarea::make('goal')
                    ->label('Tujuan')
                    ->columnSpanFull(),
                Textarea::make('objectives')
                    ->label('Sasaran')
                    ->columnSpanFull(),
                Textarea::make('target_participants')
                    ->label('Target Peserta')
                    ->columnSpanFull(),
                Textarea::make('achievements')
                    ->label('Capaian')
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->label('Gambar Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/programs')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('document')
                    ->label('Dokumen'),
                TextInput::make('budget')
                    ->label('Anggaran')
                    ->numeric(),
                TextInput::make('fiscal_year')
                    ->label('Tahun Anggaran'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'planned' => 'Direncanakan',
                        'ongoing' => 'Berjalan',
                        'completed' => 'Selesai',
                    ])
                    ->default('planned')
                    ->required(),
                Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->required(),
                TextInput::make('views')
                    ->label('Jumlah Dilihat')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Program::query()->max('sort_order') ?? 0) + 1),
                TextInput::make('meta_title')
                    ->label('Judul Meta'),
                Textarea::make('meta_description')
                    ->label('Deskripsi Meta')
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')->label('Tanggal Terbit')->default(now()),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
