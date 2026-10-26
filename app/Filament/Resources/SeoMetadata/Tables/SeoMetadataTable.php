<?php

namespace App\Filament\Resources\SeoMetadata\Tables;

use App\Filament\Actions\SafeDeleteBulkAction;
use App\Models\SeoMetadata;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeoMetadataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('model_type')
                    ->label('Jenis Konten')
                    ->formatStateUsing(fn (?string $state): string => SeoMetadata::SUPPORTED_MODELS[$state] ?? class_basename((string) $state))
                    ->searchable(),
                TextColumn::make('model_id')
                    ->label('Konten')
                    ->formatStateUsing(fn (SeoMetadata $record): string => $record->targetTitle() ?: '—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->label('Judul Meta')
                    ->searchable(),
                TextColumn::make('meta_keywords')
                    ->label('Kata Kunci Meta')
                    ->searchable(),
                ImageColumn::make('og_image')
                    ->label('Gambar OG')
                    ->disk('public'),
                TextColumn::make('canonical_url')
                    ->label('URL Kanonik')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    SafeDeleteBulkAction::make(),
                ]),
            ]);
    }
}
