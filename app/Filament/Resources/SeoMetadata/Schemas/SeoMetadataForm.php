<?php

namespace App\Filament\Resources\SeoMetadata\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeoMetadataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('model_type')
                    ->label('Jenis Model'),
                TextInput::make('model_id')
                    ->label('ID Model')
                    ->numeric(),
                TextInput::make('meta_title')
                    ->label('Judul Meta'),
                Textarea::make('meta_description')
                    ->label('Deskripsi Meta')
                    ->columnSpanFull(),
                TextInput::make('meta_keywords')
                    ->label('Kata Kunci Meta'),
                FileUpload::make('og_image')
                    ->label('Gambar OG')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/seoMetadata')
                    ->imageEditor()
                    ->maxSize(10240),
                TextInput::make('canonical_url')
                    ->label('URL Kanonik')
                    ->url(),
            ]);
    }
}
