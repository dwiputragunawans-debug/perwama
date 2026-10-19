<?php

namespace App\Filament\Resources\GalleryItems\Actions;

use App\Models\Gallery;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

/**
 * Aksi unggah beberapa foto sekaligus ke satu album galeri.
 *
 * Satu baris `gallery_items` tetap mewakili satu media, sehingga setiap foto
 * yang diunggah dibuatkan itemnya sendiri. Dengan begitu keterangan dan urutan
 * tiap foto masih dapat disunting satu per satu, dan halaman galeri publik
 * tidak perlu diubah.
 */
class UploadGalleryPhotos
{
    public static function make(?Gallery $gallery = null): Action
    {
        return Action::make('uploadPhotos')
            ->label('Unggah Beberapa Foto')
            ->icon(Heroicon::OutlinedPhoto)
            ->color('gray')
            ->modalHeading('Unggah beberapa foto')
            ->modalDescription('Pilih beberapa foto sekaligus. Setiap foto disimpan sebagai item galeri tersendiri di album yang dipilih, sehingga keterangan dan urutannya dapat diubah satu per satu.')
            ->modalSubmitActionLabel('Unggah')
            ->schema([
                // Saat aksi dibuka dari halaman Ubah Item/Ubah Galeri, albumnya
                // sudah pasti, jadi pilihan galeri tidak perlu ditampilkan.
                ...($gallery ? [] : [
                    Select::make('gallery_id')
                        ->label('Galeri')
                        ->options(fn (): array => Gallery::query()->orderBy('title')->pluck('title', 'id')->all())
                        ->searchable()
                        ->required(),
                ]),
                FileUpload::make('photos')
                    ->label('Foto')
                    ->image()
                    ->multiple()
                    ->appendFiles()
                    ->reorderable()
                    ->disk('public')
                    ->directory('uploads/gallery-items')
                    ->imageEditor()
                    ->maxSize(10240)
                    ->minFiles(1)
                    ->maxFiles(30)
                    ->required()
                    ->helperText('JPG, PNG, atau WebP; maks. 10 MB per foto. Seret gambar untuk mengatur urutan unggahan.'),
                TextInput::make('caption')
                    ->label('Keterangan')
                    ->maxLength(255)
                    ->helperText('Opsional. Bila diisi untuk beberapa foto, keterangan diberi nomor otomatis melanjutkan jumlah foto pada album.'),
            ])
            ->action(function (array $data, $livewire) use ($gallery): void {
                $album = $gallery ?? Gallery::query()->findOrFail((int) ($data['gallery_id'] ?? 0));

                /** @var array<int, string> $berkas */
                $berkas = array_values(array_filter(
                    (array) ($data['photos'] ?? []),
                    fn (mixed $path): bool => is_string($path) && filled($path),
                ));

                if ($berkas === []) {
                    return;
                }

                $keterangan = trim((string) ($data['caption'] ?? ''));
                $jumlahFoto = $album->items()->where('type', 'photo')->count();
                $urutanTerakhir = (int) ($album->items()->max('sort_order') ?? 0);
                $total = count($berkas);

                DB::transaction(function () use ($album, $berkas, $keterangan, $jumlahFoto, $urutanTerakhir, $total): void {
                    foreach ($berkas as $index => $path) {
                        $album->items()->create([
                            'type' => 'photo',
                            'file' => $path,
                            'caption' => match (true) {
                                $keterangan === '' => null,
                                // Nomor melanjutkan jumlah foto yang sudah ada
                                // agar penamaan dokumentasi tidak terulang.
                                $total > 1 => $keterangan.' — '.($jumlahFoto + $index + 1),
                                default => $keterangan,
                            },
                            'sort_order' => $urutanTerakhir + $index + 1,
                        ]);
                    }
                });

                Notification::make()
                    ->title($total.' foto ditambahkan ke '.$album->title)
                    ->success()
                    ->send();

                // Pada halaman daftar, muat ulang tabel agar item baru langsung
                // tampil tanpa perlu menyegarkan peramban.
                if (method_exists($livewire, 'resetTable')) {
                    $livewire->resetTable();
                }
            });
    }
}
