<?php

namespace App\Filament\Resources\Statistics\Schemas;

use App\Models\Statistic;
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
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (Statistic::query()->max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
