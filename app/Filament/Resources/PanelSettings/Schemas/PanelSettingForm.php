<?php

namespace App\Filament\Resources\PanelSettings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PanelSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Logo & Ikon')
                    ->description('Logo, nama, slogan, dan favicon yang tampil pada panel admin serta header website.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('brand_logo')
                            ->label('Berkas logo')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/panel')
                            ->maxSize(5120)
                            ->helperText('Disarankan PNG transparan. Logo ini juga dipakai sebagai sumber logo pada header website.'),
                        TextInput::make('brand_logo_height')
                            ->label('Tinggi logo')
                            ->maxLength(20)
                            ->default('2.5rem')
                            ->helperText('Nilai CSS, contoh: 2.5rem atau 48px.'),
                        TextInput::make('brand_name')
                            ->label('Nama brand')
                            ->maxLength(120)
                            ->helperText('Teks tebal di samping logo. Kosongkan untuk memakai nama/singkatan dari Profil Organisasi.'),
                        TextInput::make('brand_tagline')
                            ->label('Slogan')
                            ->maxLength(160)
                            ->helperText('Teks kecil di bawah nama brand. Kosongkan untuk memakai tagline dari Profil Organisasi.'),
                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/panel')
                            ->maxSize(2048)
                            ->helperText('Ikon kecil situs (disarankan 512x512 PNG). Dipakai di panel admin dan website.'),
                    ]),
                Section::make('Background Halaman Login')
                    ->description('Latar untuk halaman login dan halaman sederhana lainnya pada panel admin.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('login_background')
                            ->label('Gambar background')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/panel')
                            ->maxSize(8192)
                            ->helperText('Disarankan gambar lanskap (mis. 1920x1080). Bila kosong, warna dasar di bawah dipakai.'),
                        ColorPicker::make('login_background_color')
                            ->label('Warna dasar background')
                            ->default('#152038')
                            ->helperText('Dipakai sebagai warna dasar, atau pengganti bila gambar tidak diunggah.'),
                    ]),
            ]);
    }
}
