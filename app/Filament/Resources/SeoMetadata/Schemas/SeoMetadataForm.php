<?php

namespace App\Filament\Resources\SeoMetadata\Schemas;

use App\Models\SeoMetadata;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SeoMetadataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('model_type')
                    ->label('Jenis Konten')
                    ->options(SeoMetadata::SUPPORTED_MODELS)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('model_id', null))
                    ->helperText('Pilih jenis halaman konten yang metadata SEO-nya ingin ditimpa.'),
                Select::make('model_id')
                    ->label('Konten')
                    ->options(function (Get $get): array {
                        $modelType = $get('model_type');

                        if (! is_string($modelType) || ! isset(SeoMetadata::SUPPORTED_MODELS[$modelType])) {
                            return [];
                        }

                        $column = SeoMetadata::labelColumnFor($modelType);

                        return $modelType::query()
                            ->orderBy($column)
                            ->limit(500)
                            ->pluck($column, 'id')
                            ->all();
                    })
                    ->searchable()
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn ($rule, Get $get) => $rule->where('model_type', $get('model_type')),
                    )
                    ->helperText('Satu konten hanya memiliki satu catatan SEO.'),
                TextInput::make('meta_title')
                    ->label('Judul Meta')
                    ->maxLength(255)
                    ->helperText('Kosongkan untuk memakai judul halaman bawaan.'),
                Textarea::make('meta_description')
                    ->label('Deskripsi Meta')
                    ->rows(3)
                    ->maxLength(320)
                    ->helperText('Idealnya 120–160 karakter; kosongkan untuk memakai ringkasan konten.')
                    ->columnSpanFull(),
                TextInput::make('meta_keywords')
                    ->label('Kata Kunci Meta')
                    ->maxLength(255),
                FileUpload::make('og_image')
                    ->label('Gambar OG')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/seoMetadata')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('canonical_url')
                    ->label('URL Kanonik')
                    ->url()
                    ->maxLength(255)
                    ->helperText('Kosongkan untuk memakai URL halaman saat ini.'),
            ]);
    }
}
