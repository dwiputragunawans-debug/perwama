<?php

namespace App\Filament\Resources\OrganizationProfiles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationProfileForm
{
    /**
     * FilePond membaca imagePreviewHeight sebagai bilangan piksel. Nilai CSS
     * seperti "14rem" dipotong menjadi 14 px sehingga pratinjau gambar kolaps;
     * 224 px setara 14rem pada ukuran font dasar 16 px.
     */
    private const TINGGI_PRATINJAU_GAMBAR = '224';

    /**
     * Isian Profil Organisasi dikelompokkan per bagian agar halaman sunting
     * mudah dipindai: identitas, visi & misi beserta gambar kartu beranda,
     * deskripsi, alamat & kontak, lalu media sosial.
     *
     * Tingkat atas dipaksa satu kolom karena halaman Ubah/Buat bawaan Filament
     * memakai dua kolom; tanpa itu setiap Section hanya menempati separuh lebar
     * formulir sehingga isinya (mis. tiga gambar kartu) terjepit.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Organisasi')
                    ->description('Nama, singkatan, dan semboyan organisasi. Nilai ini dipakai pada header, footer, serta metadata situs.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama lengkap organisasi. Tampil sebagai baris kedua pada header website dan panel admin selama kolom "Slogan" di Pengaturan → Tampilan Panel dikosongkan.'),
                        TextInput::make('abbreviation')
                            ->label('Singkatan')
                            ->maxLength(255),
                        TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(255),
                        TextInput::make('founded_year')
                            ->label('Tahun Berdiri')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue((int) now()->year)
                            ->helperText('Empat angka, contoh: 1998.'),
                        Textarea::make('slogan')
                            ->label('Slogan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Section::make('Visi, Misi & Gambar Kartu Beranda')
                    ->description('Teks visi & misi sekaligus gambar untuk tiga kartu bagian "Tentang kami" pada beranda. Disarankan gambar lanskap (16:9); bila dikosongkan, kartu menampilkan bidang bermerek bawaan situs.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('vision')
                            ->label('Visi')
                            ->rows(6),
                        Textarea::make('mission')
                            ->label('Misi')
                            ->rows(6)
                            ->helperText('Tulis setiap poin misi pada baris baru.')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode("\n", $state) : (string) $state),
                        Grid::make(3)
                            ->columnSpanFull()
                            ->schema([
                                FileUpload::make('about_card_image')
                                    ->label('Gambar kartu Tentang Kami')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight(self::TINGGI_PRATINJAU_GAMBAR)
                                    ->disk('public')
                                    ->directory('uploads/about-cards')
                                    ->maxSize(10240),
                                FileUpload::make('vision_card_image')
                                    ->label('Gambar kartu Visi')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight(self::TINGGI_PRATINJAU_GAMBAR)
                                    ->disk('public')
                                    ->directory('uploads/about-cards')
                                    ->maxSize(10240),
                                FileUpload::make('mission_card_image')
                                    ->label('Gambar kartu Misi')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight(self::TINGGI_PRATINJAU_GAMBAR)
                                    ->disk('public')
                                    ->directory('uploads/about-cards')
                                    ->maxSize(10240),
                            ]),
                    ]),
                Section::make('Deskripsi')
                    ->description('Ringkasan singkat dipakai pada hero beranda, kartu "Tentang kami", footer, dan meta deskripsi halaman.')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('short_description')
                            ->label('Deskripsi Singkat')
                            ->rows(3)
                            ->helperText('Satu sampai tiga kalimat.'),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->helperText('Uraian lengkap organisasi yang tampil pada halaman Tentang Kami.'),
                    ]),

                Section::make('Alamat & Kontak')
                    ->description('Alamat kantor, kontak resmi, dan peta lokasi yang tampil pada halaman Kontak serta footer.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label('Kota')
                            ->maxLength(255),
                        TextInput::make('province')
                            ->label('Provinsi')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('map_embed')
                            ->label('Peta (Embed)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Tempelkan kode embed peta dari OpenStreetMap atau Google Maps.'),
                    ]),

                Section::make('Media Sosial')
                    ->description('Tautan akun resmi organisasi. Sertakan alamat lengkap, misalnya https://instagram.com/perwama.ina.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('facebook')
                            ->label('Facebook')
                            ->maxLength(255),
                        TextInput::make('instagram')
                            ->label('Instagram')
                            ->maxLength(255),
                        TextInput::make('youtube')
                            ->label('YouTube')
                            ->maxLength(255),
                        TextInput::make('twitter')
                            ->label('Twitter')
                            ->maxLength(255),
                        TextInput::make('tiktok')
                            ->label('TikTok')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
