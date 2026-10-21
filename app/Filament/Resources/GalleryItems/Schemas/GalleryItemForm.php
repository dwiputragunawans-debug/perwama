<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Models\GalleryItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('gallery_id')
                    ->label('Galeri')
                    ->relationship('gallery', 'title')
                    ->required()
                    // Urutan item dihitung per galeri: saat galeri dipilih,
                    // isi Urutan dengan nomor berikutnya pada galeri tersebut.
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (blank($state)) {
                            return;
                        }

                        $set('sort_order', (int) (GalleryItem::query()->where('gallery_id', $state)->max('sort_order') ?? 0) + 1);
                    }),
                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'photo' => 'Foto',
                        'video' => 'Video',
                    ])
                    ->default('photo')
                    // Jenis menentukan isian mana yang tampil (unggah foto atau
                    // URL video), jadi form harus diperbarui saat nilainya berubah.
                    ->live()
                    ->required(),
                FileUpload::make('file')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/gallery-items')
                    ->imageEditor()
                    ->maxSize(10240)
                    // Berkas lama bisa berupa path disk publik maupun tautan
                    // absolut (data awal memakai URL). Tanpa ini, FileUpload
                    // membuang nilai yang tidak ada di disk dan foto lama hilang
                    // saat item disunting.
                    ->fetchFileInformation(false)
                    ->getUploadedFileUsing(fn (string $file): array => self::pratinjauBerkas($file))
                    ->visible(fn (Get $get): bool => $get('type') !== 'video')
                    ->required(fn (Get $get): bool => $get('type') !== 'video')
                    ->validationMessages(['required' => 'Pilih foto yang akan diunggah dari komputer.'])
                    ->helperText('Pilih satu foto dari komputer (JPG, PNG, atau WebP; maks. 10 MB). Untuk menambah banyak foto sekaligus ke album ini, gunakan tombol “Unggah Beberapa Foto” di bagian atas halaman.'),
                TextInput::make('video_url')
                    ->label('URL Video')
                    ->url()
                    ->visible(fn (Get $get): bool => $get('type') === 'video')
                    ->helperText('Tempel tautan YouTube/Vimeo. Format watch, live, shorts, atau youtu.be dapat dipakai, mis. https://www.youtube.com/watch?v=...'),
                FileUpload::make('thumbnail')
                    ->label('Gambar Mini')
                    ->image()
                    ->disk('public')
                    ->directory('uploads/gallery-items')
                    ->imageEditor()
                    ->maxSize(10240)
                    ->fetchFileInformation(false)
                    ->getUploadedFileUsing(fn (string $file): array => self::pratinjauBerkas($file))
                    ->helperText('Opsional: gambar kecil untuk pratinjau. Bila kosong, galeri memakai foto di atas.'),
                Textarea::make('caption')
                    ->label('Keterangan')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Otomatis diisi nomor berikutnya setelah galeri dipilih.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(1),
            ]);
    }

    /**
     * Pratinjau berkas lama pada FileUpload.
     *
     * FileUpload hanya mengenal path pada disk, sedangkan data lama item galeri
     * menyimpan tautan absolut (mis. https://...). Helper ini menyusun data
     * pratinjau dari `media_url()` sehingga foto lama tetap tampil dan tidak
     * hilang ketika item disunting tanpa mengganti berkas.
     *
     * @return array{name: string, size: int, type: string, url: string}
     */
    private static function pratinjauBerkas(string $file): array
    {
        $nama = basename(parse_url($file, PHP_URL_PATH) ?: $file);
        $ekstensi = strtolower(pathinfo($nama, PATHINFO_EXTENSION));

        return [
            'name' => $nama,
            'size' => 0,
            // Komponen unggah hanya menampilkan gambar bila MIME-nya `image/*`.
            // Tautan lama kadang tidak memiliki ekstensi (mis. layanan gambar
            // dengan seed di URL), padahal isinya foto. Untuk tautan seperti
            // itu dipakai `image/jpeg` agar pratinjaunya tetap muncul.
            'type' => match ($ekstensi) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'avif' => 'image/avif',
                'svg' => 'image/svg+xml',
                default => str_starts_with($file, 'http') ? 'image/jpeg' : 'application/octet-stream',
            },
            'url' => (string) media_url($file),
        ];
    }
}
