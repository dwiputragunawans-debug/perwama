<?php

namespace App\Filament\Resources\OrganizationProfiles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('abbreviation')
                    ->label('Singkatan'),
                TextInput::make('tagline')
                    ->label('Tagline'),
                Textarea::make('slogan')
                    ->label('Slogan')
                    ->columnSpanFull(),
                Section::make('Visi, Misi & Gambar Kartu Beranda')
                    ->description('Isi teks visi & misi sekaligus gambar untuk tiga kartu bagian "Tentang kami" pada beranda. Gambar disimpan satu tempat dengan teksnya.')
                    ->columns(3)
                    ->schema([
                        Textarea::make('vision')
                            ->label('Visi')
                            ->rows(5)
                            ->columnSpanFull(),
                        Textarea::make('mission')
                            ->label('Misi')
                            ->rows(6)
                            ->helperText('Tulis setiap poin misi pada baris baru.')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode("\n", $state) : (string) $state)
                            ->columnSpanFull(),
                        FileUpload::make('about_card_image')
                            ->label('Gambar kartu Tentang Kami')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/about-cards')
                            ->imageEditor()
                            ->maxSize(10240)
                            ->helperText('Disarankan gambar lanskap (16:9). Bila kosong, kartu menampilkan bidang bermerek.'),
                        FileUpload::make('vision_card_image')
                            ->label('Gambar kartu Visi')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/about-cards')
                            ->imageEditor()
                            ->maxSize(10240)
                            ->helperText('Disarankan gambar lanskap (16:9). Bila kosong, kartu menampilkan bidang bermerek.'),
                        FileUpload::make('mission_card_image')
                            ->label('Gambar kartu Misi')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/about-cards')
                            ->imageEditor()
                            ->maxSize(10240)
                            ->helperText('Disarankan gambar lanskap (16:9). Bila kosong, kartu menampilkan bidang bermerek.'),
                    ]),
                Textarea::make('short_description')
                    ->label('Deskripsi Singkat')
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->label('Kota'),
                TextInput::make('province')
                    ->label('Provinsi'),
                TextInput::make('postal_code')
                    ->label('Kode Pos'),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email(),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel(),
                TextInput::make('whatsapp')
                    ->label('WhatsApp'),
                TextInput::make('founded_year')
                    ->label('Tahun Berdiri')
                    ->numeric(),
                TextInput::make('facebook')
                    ->label('Facebook'),
                TextInput::make('instagram')
                    ->label('Instagram'),
                TextInput::make('youtube')
                    ->label('YouTube'),
                TextInput::make('twitter')
                    ->label('Twitter'),
                TextInput::make('tiktok')
                    ->label('TikTok'),
                Textarea::make('map_embed')
                    ->label('Peta (Embed)')
                    ->columnSpanFull(),
            ]);
    }
}
