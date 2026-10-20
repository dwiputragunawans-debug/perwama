<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Models\MenuItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Nama menu')
                    ->required()
                    ->maxLength(60)
                    ->helperText('Teks yang tampil pada navigasi website.'),
                Select::make('route_name')
                    ->label('Tujuan halaman')
                    ->options(MenuItem::ROUTES)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('pattern', static::patternFor($state)))
                    ->helperText('Halaman website yang dibuka saat menu diklik.'),
                TextInput::make('pattern')
                    ->label('Pola penanda aktif')
                    ->required()
                    ->maxLength(60)
                    ->helperText('Pola URL untuk menandai menu sedang aktif, contoh: program*.'),
                Toggle::make('is_active')
                    ->label('Tampilkan di website')
                    ->helperText('Matikan untuk menyembunyikan menu ini dari navigasi website.')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Record baru otomatis diletakkan di urutan terakhir.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(fn (): int => (int) (MenuItem::query()->max('sort_order') ?? 0) + 1),
                TextInput::make('key')
                    ->label('Kunci unik')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(60)
                    ->helperText('Penanda internal untuk seeder, tidak tampil di website.'),
            ]);
    }

    /**
     * Pola URL bawaan untuk tiap rute, dipakai saat mengisi otomatis kolom pola.
     */
    private static function patternFor(?string $route): string
    {
        return match ($route) {
            'home' => '/',
            'about' => 'tentang',
            'program.index' => 'program*',
            'region.index' => 'wilayah*',
            'activity.index' => 'kegiatan*',
            'story.index' => 'cerita*',
            'news.index' => 'berita*',
            'publication.index' => 'publikasi*',
            'gallery.index' => 'galeri*',
            'video.index' => 'galeri/video*',
            'join' => 'bergabung',
            'contact' => 'kontak',
            default => '',
        };
    }
}
