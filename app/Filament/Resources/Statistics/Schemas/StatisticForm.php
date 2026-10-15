<?php

namespace App\Filament\Resources\Statistics\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StatisticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Kunci')
                    ->required(),
                TextInput::make('label')
                    ->label('Label')
                    ->required(),
                TextInput::make('value')
                    ->label('Nilai')
                    ->required(),
                TextInput::make('suffix')
                    ->label('Sufiks'),
                TextInput::make('icon')
                    ->label('Ikon'),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
