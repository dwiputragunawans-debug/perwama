<?php

namespace App\Providers\Filament;

use App\Models\PanelSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            /*
             * Setelah menekan tombol "Buat" maupun "Simpan", admin kembali ke
             * daftar record (index) masing-masing resource, bukan berhenti di
             * halaman edit.
             */
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            ->brandName(fn (): string => PanelSetting::current()->displayName())
            /*
             * Logo, nama, slogan, dan favicon panel dibaca dari pengaturan
             * (Pengaturan -> Tampilan Panel). Bila logo belum diunggah, panel
             * menampilkan nama brand.
             */
            ->brandLogo(fn (): ?string => media_url(PanelSetting::current()->brand_logo))
            ->brandLogoHeight(fn (): string => PanelSetting::current()->brand_logo_height ?: '2.5rem')
            ->favicon(fn (): ?string => media_url(PanelSetting::current()->favicon))
            /*
             * Penyesuaian tampilan: logo lebih besar, teks nama & slogan di
             * samping logo, sidebar putih, background halaman login, dan warna
             * tombol utama (lihat resources/views/filament/appearance-styles).
             */
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): View => view('filament.appearance-styles'),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): View => view('filament.brand-text'),
            )
            /*
             * Animasi loading panel: overlay + spinner saat formulir dikirim,
             * halaman berpindah, atau aksi Livewire berjalan (lihat
             * resources/views/filament/loading-indicator.blade.php). Tujuannya
             * memberi umpan balik bahwa proses sedang berjalan sehingga
             * pengguna tidak menekan tombol dua kali.
             */
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): View => view('filament.loading-indicator'),
            )
            ->colors([
                'primary' => Color::hex('#1F2A44'),
                'gold' => Color::hex('#D9B44A'),
                'success' => Color::hex('#3F6B45'),
                'warning' => Color::hex('#D9B44A'),
                'danger' => Color::hex('#B85C3A'),
            ])
            ->font('Source Sans 3')
            /*
             * Susunan grup sidebar dibuat mengikuti menu pada website
             * (Beranda, Tentang, Program, Wilayah, Kegiatan, Cerita, Berita,
             * Galeri, Kontak) ditambah "Bergabung" untuk formulir pendaftaran
             * dan "Pengaturan" untuk hal teknis panel.
             *
             * Tujuannya: seluruh konten yang tampil pada satu halaman website
             * berada dalam satu grup, sehingga pengelola tidak perlu berpindah
             * ke beberapa menu admin hanya untuk menyunting satu halaman.
             *
             * Saat menambah resource baru, pilih grup yang sesuai daftar ini
             * (dan daftar di App\Models\MenuItem::DEFAULTS).
             */
            ->navigationGroups([
                'Beranda',
                'Tentang',
                'Program',
                'Wilayah',
                'Kegiatan',
                'Cerita',
                'Berita',
                'Galeri',
                'Kontak',
                'Bergabung',
                'Pengaturan',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            /*
             * Dasbor dan widgetnya memakai kelas milik proyek ini, bukan bawaan
             * Filament: dasbor disusun tiga kolom (App\Filament\Pages\Dashboard)
             * dan diisi kartu ringkasan pengunjung, kartu ringkasan organisasi,
             * grafik trafik, daftar anggota terbaru, serta grafik pertumbuhan
             * anggota (App\Filament\Widgets). Keduanya ditemukan otomatis di
             * sini, sehingga tidak perlu didaftarkan satu per satu.
             */
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
