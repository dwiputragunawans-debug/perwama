<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Panel Admin (Filament)

Panel admin tersedia pada `/admin`. Jumlah admin **tidak dibatasi** — setiap
akun dengan flag "Akses Admin" dapat masuk dan mengelola seluruh konten.

Menambah pengelola baru (dua cara):

1. Lewat panel: login sebagai admin → **Pengguna → Tambah Pengguna** → isi nama,
   email, dan kata sandi → pastikan "Akses Admin" aktif → **Simpan**.
2. Lewat seeder: tambahkan entri baru pada `admins` di `config/perwama.php`,
   lalu jalankan `php artisan db:seed --class=AdminUserSeeder`. Seeder bersifat
   idempotent: tidak menggandakan akun dan tidak menimpa kata sandi yang sudah
   diganti.

**Kata sandi admin tidak punya nilai bawaan.** Isi `ADMIN_PASSWORD` pada `.env`
(minimal 12 karakter, tidak mudah ditebak) sebelum menjalankan seeder. Pada
environment selain `local`/`testing`, seeder berhenti dengan pesan galat bila
variabel itu kosong atau lemah — server tidak akan pernah membuat akun dengan
kata sandi yang diketahui publik. Di `local`/`testing` yang belum mengisi
variabel tersebut, seeder membuat kata sandi acak dan menampilkannya sekali di
terminal.

Bila sebuah lingkungan pernah memakai kata sandi bawaan versi lama
(`password`), ganti kata sandi akun tersebut sekarang juga — mis. lewat panel
**Pengguna → Ubah Pengguna**, atau dengan menjalankan
`php artisan db:seed --class=AdminUserSeeder` setelah `ADMIN_PASSWORD` diatur
(seeder hanya mengisi kata sandi akun **baru**, jadi untuk akun yang sudah ada
gunakan panel atau `php artisan tinker`).

Pengamanan: admin tidak dapat mencabut akses admin akun sendiri, dan admin
terakhir tidak dapat dihapus/dicabut — lihat `app/Policies/UserPolicy.php`,
`User::isLastAdmin()`, serta `AdminUserSeeder`.

Setelah menekan tombol **Buat** maupun **Simpan**, panel kembali ke daftar
record (bukan berhenti di halaman edit) — diatur lewat
`->resourceCreatePageRedirect('index')` dan `->resourceEditPageRedirect('index')`
pada `AdminPanelProvider`.

Label isian pada formulir **Buat** dan **Ubah** selalu ditulis eksplisit dalam
bahasa Indonesia lewat `->label('...')` (mis. `->label('Nama Lengkap')`). Ini
perlu karena Filament menurunkan label otomatis dari nama kolom teknis,
sehingga `full_name` tampil sebagai "Full name", `birth_place` sebagai
"Birth place", dan seterusnya. Nama kolom tetap dipakai sebagai kunci data;
hanya teks labelnya yang di-Indonesia-kan. Uji
`Tests\Feature\AdminFormLanguageTest` menjaga agar tiap field pada seluruh
resource memiliki label eksplisit.

Aturan yang sama berlaku untuk judul kolom pada tabel daftar: setiap kolom
(`TextColumn`, `IconColumn`, `ImageColumn`, dll.) juga diberi `->label('...')`
eksplisit dengan istilah yang selaras dengan label formulirnya. Kolom bawaan
memakai istilah tetap, mis. `created_at` → "Dibuat", `updated_at` →
"Diperbarui", `published_at` → "Tanggal Terbit", `is_active` → "Aktif",
`is_featured` → "Unggulan", `sort_order` → "Urutan", dan kolom relasi seperti
`region.name` → "Wilayah". Uji `Tests\Feature\AdminTableLanguageTest` menjaga
agar tiap kolom pada seluruh resource memiliki label eksplisit dan halaman
daftar tidak lagi menampilkan judul kolom berbahasa Inggris.

### Indikator Proses (Loading) Panel

Panel memakai navigasi halaman penuh (tanpa SPA) dan formulir Livewire. Tanpa
umpan balik, pengguna mengira tombol tidak berfungsi lalu menekannya lagi —
akibatnya record tersimpan dua kali. Indikator ditambahkan untuk itu:
`resources/views/filament/loading-indicator.blade.php`, disuntikkan ke setiap
halaman panel (termasuk halaman login) lewat `PanelsRenderHook::BODY_START`
pada `AdminPanelProvider`.

Tampilannya dua bentuk:

- **Overlay penuh** (kartu + cincin berputar navy/emas) untuk proses yang
  mengubah halaman: kirim formulir, konfirmasi aksi/hapus, dan perpindahan
  halaman. Ketika aktif, lapisan `#panel-loading` menutup layar
  (`pointer-events: auto`), sehingga klik berikutnya tidak sampai ke tombol.
- **Bilah progres tipis** di tepi atas layar untuk permintaan latar seperti
  pencarian dan filter tabel. Layar tidak dikunci karena pengguna masih perlu
  mengetik; bilah hanya menandakan data sedang dimuat.

Perilaku penting:

- Overlay muncul setelah **150 ms** (`REVEAL_DELAY`) agar proses yang selesai
  seketika tidak berkedip, tetapi klik sudah diblokir sejak milidetik pertama.
- Kiriman formulir kedua dalam **1000 ms** (`DOUBLE_SUBMIT_WINDOW`) dibatalkan
  dengan `preventDefault()` + `stopImmediatePropagation()` sebelum Livewire
  mengirim permintaan, sehingga klik ganda hanya menyimpan satu record.
- Permintaan dipantau lewat `Livewire.hook('request')`. Panggilan metode
  komponen (mis. `mountAction`, `delete`, `create`) memakai overlay; permintaan
  yang hanya berisi metode internal Livewire (`$commit`, `$refresh`, `$set`)
  memakai bilah progres, dan polling widget tanpa interaksi pengguna tidak
  menampilkan apa pun.
- Klik tautan internal diintersepsi: tautan eksternal, `download`,
  `target="_blank"`, klik dengan Ctrl/Cmd/Shift/Alt, dan jangkar halaman yang
  sama dibiarkan apa adanya. Untuk mengecualikan tautan tertentu dari
  indikator, tambahkan atribut `data-no-panel-loading`.
- Pengaman: indikator dilepas otomatis setelah **20 s** (`SAFETY_TIMEOUT`) bila
  respons tidak pernah datang, dan **5 s** (`NAVIGATION_TIMEOUT`) bila
  perpindahan halaman dibatalkan (mis. dialog "perubahan belum disimpan").
  Halaman yang dipulihkan dari cache riwayat ikut dibersihkan.
- Menghormati `prefers-reduced-motion`: putaran diperlambat dan transisi
  dimatikan, indikator tetap tampil.

Uji `php artisan test --filter=AdminLoadingIndicatorTest` memastikan indikator
tersuntik di halaman login maupun halaman panel, memblokir klik ganda,
mengawasi tautan, dan memantau permintaan Livewire.

### Kategori Album (Master Data)

Pilihan **Kategori Album** tidak lagi ditulis di dalam kode. Kategori dikelola
dari **Galeri → Kategori Album** (tabel `album_categories`, model
`App\Models\AlbumCategory`): nama, slug, deskripsi, urutan, dan status aktif.
Form **Tambah/Ubah Galeri** mengambil pilihan langsung dari master data ini
lewat relasi `album_category_id`, sehingga kategori baru langsung tersedia
tanpa mengubah kode.

Halaman publik `/galeri?album={slug}` tetap memakai slug kategori. Delapan
kategori lama (`hut_perwama_ina`, `pelatihan`, `bakti_sosial`, `seminar`,
`rapat_organisasi`, `kunjungan`, `kegiatan_daerah`, `budaya`) dipindahkan oleh
migrasi beserta penugasan galeri yang sudah ada, jadi tautan lama tidak rusak.
Kategori yang dihapus membuat galeri terkait tidak berkategori (label jatuh ke
"Galeri"), bukan ikut terhapus.

### Profil Organisasi (Data Tunggal)

**Tentang → Profil Organisasi** menyimpan data organisasi pada **satu baris**
tabel `organization_profiles` (`OrganizationProfile::current()` memakai
`firstOrCreate` pada id 1). Karena itu tombol **Buat Profil Organisasi** hanya
tersedia selama tabel masih kosong: `OrganizationProfileResource` menimpa
`getCreateAuthorizationResponse()` dan menolak pembuatan data begitu ada satu
baris. Filament memakai respons yang sama untuk menyembunyikan aksi Buat pada
halaman daftar sekaligus menolak `/admin/organization-profiles/create` dengan
**403** — `Resource::canCreate()` bawaan memang diturunkan dari respons itu,
jadi satu penimpaan sudah mencakup keduanya. Halaman panel sendiri memanggil
`OrganizationProfile::current()` lewat `PanelSetting::displayName()` (fallback
nama & tagline), sehingga baris profil dibuat otomatis pada kunjungan pertama
ke panel dan tombol tersebut praktis selalu tersembunyi.

Formulir suntingnya dikelompokkan menjadi lima bagian agar tidak lagi berupa
satu kolom isian yang panjang. Tingkat atas dipaksa satu kolom
(`->columns(1)`), sebab halaman Ubah/Buat bawaan Filament menyuntikkan dua
kolom — sebelumnya itu membuat bagian **Visi, Misi & Gambar Kartu Beranda**
hanya selebar separuh formulir sehingga tiga unggahan gambar tampak terjepit:

| Bagian | Isian |
| --- | --- |
| Identitas Organisasi | `name`, `abbreviation`, `tagline`, `founded_year`, `slogan` |
| Visi, Misi & Gambar Kartu Beranda | `vision`, `mission`, lalu `about_card_image`, `vision_card_image`, `mission_card_image` dalam satu grid tiga kolom (`imagePreviewHeight('224')` — piksel, karena FilePond tidak menerima satuan CSS — agar kartu sama besar) |
| Deskripsi | `short_description`, `description` |
| Alamat & Kontak | `address`, `city`, `province`, `postal_code`, `email`, `phone`, `whatsapp`, `map_embed` |
| Media Sosial | `facebook`, `instagram`, `youtube`, `twitter`, `tiktok` |

Visi & Misi tetap berada pada satu bagian bersama ketiga gambar kartu beranda
(seperti sebelumnya) — dijaga oleh
`Tests\Feature\OrganizationProfileCardImagesTest`, sedangkan susunan bagian,
tata letak grid gambar, dan aturan tombol Buat dijaga
`Tests\Feature\OrganizationProfileAdminFormTest`.

### Tokoh Organisasi

**Tentang → Tokoh Organisasi** mengelola kartu pendiri/tokoh pada halaman
Tentang Kami (`organization_figures`). Setiap baris berisi `name`, `position`
(bawaan **Pendiri**), `photo`, `sort_order`, dan `is_active`. Foto diunggah dari
komputer ke disk `public` pada `uploads/organization-figures`; bila kosong,
kartu publik menampilkan bidang inisial nama sehingga tetap rapi sebelum foto
asli tersedia. Urutan kecil tampil lebih dulu, `is_active = false` menyembunyikan
tokoh dari situs, dan formulir Buat otomatis mengisi urutan setelah nilai
terbesar. Tata letak publik menyediakan empat kolom sejajar pada layar lebar
(`sm:grid-cols-2` di tablet), tetapi jumlah tokoh mengikuti data aktif — bukan
dipaksa empat. Seeder mengisi empat nama pendiri yang tercatat (Robine Inka
Caecilia M. P., Johanna Pietersz, Evie Poetiray, Petrosina Jacoba (Dee)
Tuwaahatu - Amahorseja); tokoh berikutnya ditambahkan lewat panel. Dijaga oleh
`Tests\Feature\OrganizationFigureTest` dan `Tests\Feature\SortOrderTest`.

### Identitas Organisasi (Data Tunggal)

**Tentang → Identitas Organisasi** menyimpan judul, deskripsi, dan gambar
pendukung bagian identitas pada halaman Tentang Kami dalam satu baris tabel
`organization_identities` (`OrganizationIdentity::current()`, id 1). Menu ini
terpisah dari Profil Organisasi dan hanya memiliki form sunting — tidak ada
aksi Buat atau Hapus. Isian **Judul** berisi nama bagian (bawaan
`Mars PERWAMA INA`), sedangkan **Deskripsi** berisi lirik: satu baris per lirik
dan satu baris kosong antarbait. Baris pertama yang diawali `Ciptaan:` otomatis
tampil sebagai kredit di bawah judul. Bagian **Gambar Pendukung** menerima
beberapa gambar sekaligus (`images`, JSON) — misalnya lembar partitur — yang
dapat diurutkan dengan menyeret; berkas disimpan pada disk `public` di
`uploads/organization-identities`. Migrasi membuat baris bawaan sehingga bagian
langsung tayang setelah `php artisan migrate`.

## Dasbor Panel (Ringkasan Pengunjung & Organisasi)

Halaman pertama setelah masuk (**Dasbor**, `App\Filament\Pages\Dashboard`)
merangkum dua hal sekaligus: trafik portal publik dan perkembangan organisasi.
Dasbor disusun dalam **tiga kolom**, sehingga kartu statistik serta grafik trafik
memakai lebar penuh, sedangkan **Pendaftaran Anggota Terbaru** (2 kolom) tampil
berdampingan dengan grafik **Pertumbuhan Anggota** (1 kolom).

Bagian **Ringkasan Pengunjung Website** dihitung dari kunjungan nyata:

| Kartu/Grafik | Sumber data |
| --- | --- |
| Pengunjung Unik | jumlah sidik jari peramban berbeda pada tabel `page_views` bulan ini |
| Total Halaman Dilihat | jumlah baris `page_views` bulan ini |
| Rata-rata Waktu Akses | rata-rata `duration_seconds` dari laporan peramban |
| Trafik Pengunjung Harian | jumlah kunjungan per hari, dengan saringan **Bulan ini** / **Bulan lalu** |

Setiap kartu menampilkan arah perubahan dibanding bulan sebelumnya: panah naik
berwarna hijau (`success`) bila bertambah, panah turun berwarna merah (`danger`)
bila berkurang, dan dibiarkan abu-abu tanpa ikon bila belum ada pembanding.

Bagian **Ringkasan Organisasi** memakai data yang sudah ada di panel:

| Kartu/Grafik | Sumber data |
| --- | --- |
| Total Anggota | tabel `members`; keterangan berisi persentase anggota baru bulan ini |
| Wilayah / Cabang | `regions` aktif ditambah `branches` aktif; keterangan berisi cabang baru bulan ini |
| Berita Diterbitkan | `news` yang sudah terbit pada bulan berjalan |
| Total Kegiatan | `activities` ditambah `events`; keterangan berisi agenda yang sedang berlangsung |
| Pendaftaran Anggota Terbaru | lima anggota terakhir beserta domisili dan statusnya, dengan tautan **Lihat semua** ke menu Anggota |
| Pertumbuhan Anggota | jumlah anggota baru pada enam bulan terakhir |

### Cara kunjungan dicatat

- `App\Http\Middleware\RecordPageView` (terdaftar pada `bootstrap/app.php`)
  mencatat setiap **halaman publik yang berhasil dibuka**: hanya permintaan
  `GET` berstatus 200 dan berjenis HTML.
- Yang diabaikan: halaman panel `/admin`, endpoint Livewire, penyegar sesi,
  permintaan POST, halaman galat, perayap mesin pencari, dan pengelola yang
  sedang masuk — agar angka pengunjung tidak tercampur aktivitas pengurus.
- Alamat IP **tidak** disimpan. Yang disimpan adalah sidik jari berupa hash
  SHA-256 dari IP + user agent (`App\Support\VisitorFingerprint`), sehingga
  pengunjung unik tetap dapat dihitung tanpa menyimpan data pribadi.
- Lama kunjungan hanya diketahui peramban, sehingga `resources/js/app.js`
  melaporkannya sekali saat halaman ditinggalkan (tab disembunyikan/ditutup)
  ke `POST /jejak-kunjungan` (`App\Http\Controllers\VisitController`) dengan
  menyertakan token CSRF, tanpa mengganggu pemuatan halaman. Angka terbesar
  yang bertahan itulah yang disimpan, dan laporan dibatasi satu jam agar tab
  yang dibiarkan terbuka semalaman tidak membuat rata-rata tidak wajar.
- Bila sesi pengunjung sudah berakhir saat laporan dikirim, laporan dialihkan
  ke kunjungan terakhir dari peramban yang sama dalam 30 menit terakhir.

Perhitungan dikumpulkan pada `App\Support\VisitorStatistics` dan
`App\Support\OrganizationStatistics`, sedangkan tampilannya pada
`App\Filament\Widgets` (kartu, grafik, dan daftar). Keduanya ditemukan otomatis
lewat `discoverWidgets`/`discoverPages` pada `AdminPanelProvider`, memakai tema
panel (font **Source Sans 3**, warna brand navy `#1F2A44`, emas `#D9B44A`, hijau
`#3F6B45`, dan terracotta `#B85C3A`), serta seluruh label berbahasa Indonesia
dengan format angka Indonesia (mis. `12.450`, `2 mnt 45 dtk`, `8,5%`).
`Tests\Feature\AdminDashboardTest` dan `Tests\Feature\VisitorTrackingTest`
menjaga angka, tata letak, dan aturan pencatatannya.

Tabel `page_views` bertambah seiring kunjungan sehingga perlu dibersihkan
berkala, misalnya menyisakan satu tahun terakhir:

```bash
php artisan migrate
php artisan tinker
>>> App\Models\PageView::query()->where('viewed_at', '<', now()->subYear())->delete();
```

```bash
npm run build   # wajib setelah mengubah resources/js/app.js
```

## Menu Navigasi Website (Dinamis)

Susunan menu pada navigasi situs publik dikelola dari panel:
**Pengaturan → Menu Website**. Setiap baris dapat:

- dimunculkan/disembunyikan lewat kolom **Tampil** (toggle di daftar),
- diubah namanya, tujuan halaman, urutan, serta pola penanda aktifnya.

Data disimpan pada tabel `menu_items` (`App\Models\MenuItem`). Bila tabel belum
diisi, menu bawaan pada `MenuItem::DEFAULTS` dipakai otomatis sehingga situs
tidak pernah kehilangan navigasi. Untuk menyiapkan menu bawaan pada database
yang sudah berjalan:

```bash
php artisan migrate
php artisan db:seed --class=MenuItemSeeder
```

`MenuItemSeeder` bersifat idempotent (`firstOrCreate`) sehingga aman dijalankan
ulang dan tidak menimpa penyesuaian yang sudah dilakukan pengelola.

### Pengelompokan menu pada header

Sembilan menu tersebut tidak lagi tampil berjajar pada header, melainkan
dirapikan menjadi **lima menu utama** melalui `MenuItem::GROUPS`:

| Menu utama | Isi |
| --- | --- |
| Beranda | `home` (tautan tunggal) |
| Profil | `about` (Tentang), `region` (Wilayah) |
| Program & Kegiatan | `program`, `activity` (Kegiatan), `story` (Cerita) |
| Publikasi | `news` (Berita), `gallery` (Galeri) |
| Kontak | `contact` (tautan tunggal) |

- Kelompok berisi lebih dari satu menu tampil sebagai **menu tarik-turun**:
  tombol pembuka dengan ikon panah, membuka panel berisi menu anggotanya.
  Panel terbuka saat kursor diarahkan, saat tombolnya difokus dengan papan
  ketik, atau saat diklik; menutup dengan klik di luar, tombol `Esc`, atau
  klik ulang. Perilaku ini ditangani `resources/js/app.js` (atribut ARIA dan
  posisi panel) dengan CSS padanan di `resources/css/app.css`.
- Kelompok bertaut tunggal (Beranda, Kontak) tampil sebagai tautan biasa dan
  memakai nama menunya sendiri, sehingga penyesuaian nama di panel langsung
  terpakai.
- Nama kelompok hanya diatur di kode (`MenuItem::GROUPS`); nama tiap menu,
  tujuan halaman, urutan, dan status tampil tetap diatur pengelola dari panel.
- Menu yang dimatikan tidak ditampilkan. Bila seluruh isi sebuah kelompok
  dimatikan, kelompok itu ikut hilang dari header. Menu yang tidak tercantum
  pada `GROUPS` tetap tampil sebagai tautan tunggal agar menu tambahan dari
  panel tidak pernah hilang.
- Halaman anggota membuat kelompoknya ikut ditandai aktif (mis. membuka
  `/wilayah` menandai **Profil** aktif). Pola penanda aktif boleh memuat
  beberapa pola sekaligus dan dihitung oleh helper `menu_aktif()`.

Pada perangkat seluler seluruh kelompok diuraikan menjadi daftar tegak: nama
kelompok sebagai judul, anggotanya diindentasi di bawahnya.

`GROUPS` sengaja dipisah dari `DEFAULTS`: `DEFAULTS` adalah sembilan bagian
konten yang sekaligus menjadi grup sidebar panel admin, sedangkan `GROUPS`
hanya mengatur bagaimana menu-menu itu dikelompokkan pada header website.
Karena itu pengelompokan ini tidak mengubah struktur basis data maupun panel.

## Tampilan Panel & Website (Logo, Branding, Favicon, Hero)

Tampilan panel admin dikelola dari **Pengaturan → Tampilan Panel** (data pada
tabel `panel_settings`, model `App\Models\PanelSetting` — baris tunggal):

- **Logo & Ikon** — unggah logo (`brand_logo`), atur tingginya
  (`brand_logo_height`), teks **Nama brand** (`brand_name`), **Slogan**
  (`brand_tagline`), serta **Favicon** (`favicon`). Logo dan teks ini juga
  dipakai pada **header website** (sumber yang sama). Bila `brand_name` /
  `brand_tagline` kosong, nilainya diambil dari **Profil Organisasi**.
  Logo ditampilkan lebih besar dari bawaan: halaman login 1.8x dan di dalam
  panel (topbar & sidebar) 1.3x dari tinggi yang diatur. Nama dan slogan tampil
  di samping logo pada topbar panel.
- **Background Halaman Login** — unggah gambar (`login_background`) dan/atau
  pilih warna dasar (`login_background_color`). Gambar dipakai sebagai latar
  halaman login/sederhana; bila tidak ada gambar, warna dasar yang dipakai.

Gambar hero beranda diatur terpisah pada **Pengaturan → Tampilan Website**
(tabel `website_settings`, model `App\Models\WebsiteSetting` — baris tunggal):

- **Gambar Hero Beranda** — unggah beberapa gambar (`hero_images`, dapat
  diseret untuk mengubah urutan). Gambar dipakai sebagai carousel latar hero di
  beranda. Bila kosong, **cover galeri** dipakai sebagai cadangan.
- Favicon (`favicon`) dipakai di panel admin (`->favicon()`) sekaligus pada
  `<link rel="icon">` website.

Nilai tersebut disuntikkan oleh `AdminPanelProvider` (lewat `brandName()` /
`brandLogo()` / `brandLogoHeight()` / `favicon()` serta render hook
`panels::styles.after` dan `panels::topbar.logo.after` pada
`resources/views/filament/appearance-styles.blade.php` dan
`resources/views/filament/brand-text.blade.php`), sehingga dapat diubah tanpa
mengubah kode.

Tombol aksi utama (primary) dipaksa memakai warna brand **`#1F2A44` dengan teks
putih** pada mode terang, karena bawaan Filament v5 memakai shade terang
(`primary-400`) dengan teks gelap. Tombol `outlined` (mis. "Batal") tetap netral.

Ukuran huruf **seluruh panel** dinaikkan satu tingkat dari skala bawaan Filament
agar nyaman dibaca pengelola yang usianya sudah berumur:

| Tingkat | Bawaan | Panel admin |
| --- | --- | --- |
| `text-xs` | 12px | **14px** |
| `text-sm` | 14px | **16px** |
| `text-base` | 16px | **18px** |
| `text-lg` | 18px | **20px** |
| `text-xl` | 20px | **24px** |
| `text-2xl` | 24px | **30px** |
| `text-3xl` | 30px | **36px** |

Skala ditimpa lewat variabel `--text-*` (beserta `--text-*--line-height`) pada
`resources/views/filament/appearance-styles.blade.php`. Cara ini dipilih karena
seluruh gaya bawaan panel (`fi-btn`, `fi-ta-*`, `fi-fo-*`, `fi-prose`, dst.)
memakai `font-size: var(--text-*)`, sehingga satu penimpaan berlaku untuk semua
komponen. Ukuran akar (`html`) **tidak** diubah supaya jarak, lebar sidebar, dan
tinggi baris tabel tetap; teks yang mengikuti ukuran dasar naik lewat `body`
(18px), dan ukuran tetap dari pustaka pihak ketiga (tooltip *tippy*, angka
filter rentang *noUiSlider*) ikut dinaikkan.

```bash
php artisan migrate
```

## Halaman Publik

- **Beranda** menampilkan hero dengan **latar penuh** (*full-bleed background
  carousel*) memakai gambar dari **Pengaturan → Tampilan Website** (cadangan:
  cover galeri). Teks hero dipusatkan di atas overlay gradien gelap agar tetap
  kontras; slideshow berjalan otomatis (6 detik) dan dapat dinavigasi lewat
  tombol panah naik/turun di sisi kanan serta indikator di tengah bawah. Hero
  mengisi satu layar penuh di bawah header sticky (`min-height` memakai `svh`
  dikurangi tinggi header), tanpa kartu **Agenda terdekat**.
  Isi hero memakai padding bawah lebih besar di ponsel (`pb-20` = 5rem,
  sedangkan layar besar tetap `md:py-16` = 4rem): tombol CTA menumpuk selebar
  layar pada ponsel, sehingga dengan padding 3rem titik indikator (dipatok
  `bottom: 2.5rem` di `resources/css/app.css`) hanya menyisakan ~8px jarak ke
  tombol "Jelajahi Program". Dengan `pb-20` tombol selalu berada 5rem di atas
  dasar hero sehingga jeda ke titik indikator tetap ±2.1rem — dijaga oleh
  `Tests\Feature\PublicPagesTest::test_hero_beranda_memesan_ruang_bawah_untuk_indikator`.
- **Logo header** website memakai sumber yang sama dengan logo panel admin
  (Pengaturan → Tampilan Panel); favicon website mengikuti pengaturan favicon.
- **Kartu "Tentang kami"** pada beranda (Tentang Kami, Visi, Misi) memakai
  gambar yang diunggah pengelola lewat **Tentang → Profil Organisasi**
  (`about_card_image`, `vision_card_image`, `mission_card_image`) — berada pada
  satu bagian formulir dengan isian **Visi & Misi** (`vision`, `mission`),
  sehingga teks dan foto disunting dari satu tempat. Bila gambar dikosongkan,
  kartu memakai bidang bermerek bawaan situs.
- Tombol **"Lihat Selengkapnya"** pada ketiga kartu "Tentang kami" memakai
  tombol emas utama situs (`btn btn-gold` → `--color-gold` `#d9b44a` dengan
  teks `--color-navy-deep`), sama persis dengan tombol ajakan pada hero
  ("Bergabung Bersama Kami"), sehingga warnanya selalu identik.
- **Bagian "Berita"** pada beranda memakai tata letak dua kolom: satu berita
  **unggulan** (foto besar, kategori, tanggal, judul besar, ringkasan, dan
  tautan "Baca berita") di kolom kiri, lalu tiga **kartu ringkas berfoto
  persegi** (`.card-media-square`) di kolom kanan. Beranda memuat empat berita
  terbaru (`take(4)`), memakai warna, tipografi, dan bidang gambar yang sama
  dengan kartu di halaman lain.
- **Penutup beranda ("Bergabung")** memakai jeda atas dan jeda bawah yang sama
  besar supaya kartu tidak tampak menempel pada bagian "Berita" sambil
  melayang jauh dari pita footer. Bagian ini memakai
  `pt-20 pb-40 md:pt-25 md:pb-50`: padding atas 5rem/6.25rem (sama dengan
  `.section-y`) ditambah padding bawah bagian sebelumnya — juga 5rem/6.25rem —
  menghasilkan jeda atas 10rem/12.5rem (160px/200px), dan padding bawah kartu
  dipatok sebesar jumlah itu. Jadi jaraknya ke bagian di atas = jarak ke footer
  = ritme antar bagian lain di beranda, bukan lagi 8rem/12rem seperti pola
  `pb-24 md:pb-32` yang lama. Pola ini juga dipakai kartu penutup halaman Cerita.
  CTA halaman Tentang Kami kini diikuti bagian Identitas Organisasi, sehingga
  padding bawahnya memakai satu langkah `.section-y` (lihat bagian Halaman
  Tentang Kami di bawah).
- **Pembungkus navigasi halaman** pada halaman daftar (Berita, Cerita, Program,
  Publikasi, Galeri, Video, Kegiatan) hanya dirender bila `hasPages()` — yaitu
  bila memang ada halaman lain. Tanpa penjagaan itu, `{{ $x->links() }}` yang
  kosong tetap menyisakan `<div class="mt-12">` / `mt-14` dengan margin yang
  runtuh (*margin collapse*) sehingga menambah 3–3.5rem ruang kosong di dalam
  bagian terakhir halaman: mis. jarak ke footer pada `/berita` menjadi 128px
  (bukan 80px) dan jeda di atas kartu penutup `/cerita` menjadi 208px/248px
  (bukan 160px/200px). Dijaga oleh
  `Tests\Feature\PublicPagesTest::test_daftar_satu_halaman_tidak_menyisakan_pembungkus_paginasi_kosong`.
- Tipografi situs memakai **Poppins, sans-serif** (`resources/css/app.css`).
- Formulir pada halaman **Bergabung** ditampilkan rata tengah agar lebih rapi.
- **Ketahanan formulir:** token CSRF disegarkan berkala (dan saat tab kembali
  aktif) lewat endpoint `/sesi/segar` (`resources/js/app.js`), sehingga formulir
  yang lama dibiarkan terbuka tidak gagal dengan *419 Page Expired*. Bila sesi
  benar-benar berakhir, pengguna melihat halaman 419 yang ramah
  (`resources/views/errors/419.blade.php`) dengan tautan kembali ke formulir.

### Halaman Tentang Kami

Halaman `/tentang` disusun mengikuti acuan tampilan, sementara warna, tipografi,
dan komponennya tetap memakai tema situs:

- **Hero bersama:** memakai komponen `<x-page-hero>` yang sama dengan halaman
  lain (kecuali beranda) — eyebrow bergaris emas, nama organisasi, ringkasan
  profil, dan breadcrumb **Beranda / Tentang Kami**. Foto hero memakai
  `about_card_image`; bila belum diunggah, cover galeri pertama dipakai sebagai
  latar tipis, dan bila keduanya kosong hero tampil tanpa foto.
- **Label bagian tidak berulang:** hero memakai label **Profil Organisasi**,
  sedangkan bagian profil di bawahnya memakai **Sekilas Organisasi** — sehingga
  tidak ada label yang tampil dua kali pada satu halaman. Tagline organisasi dan
  tahun berdiri (`founded_year`) tampil sebagai satu baris keterangan di bawah
  judul **Sekilas Organisasi** (menggantikan lencana "Tahun Berdiri" pada hero
  dua kolom yang lama).
- **Narasi dan sekretariat bertumpuk:** deskripsi profil ditampilkan selebar
  kontainer (tanpa batas `max-w-4xl`), lalu kartu **Sekretariat** berada di
  bawahnya (bukan di samping). Kartu ini membagi alamat dan kontak menjadi dua
  kolom pada layar lebar, sehingga deskripsi panjang — termasuk gambar dan
  daftar dari editor — mengisi seluruh lebar section tanpa ruang kosong di kanan.
- **Pita statistik:** empat angka pertama dari **Tentang → Statistik** tampil
  pada satu kartu putih (kelas `.stat-band`, melintang penuh) di dasar bagian
  **Sekilas Organisasi** — nilai besar navy dengan akhiran emas, label kecil di
  bawahnya, dan garis pemisah tipis antarkolom pada layar lebar. Angka kelima
  dan seterusnya tampil pada kartu **Capaian lainnya** di bawah profil —
  sehingga seluruh angka tetap terlihat tanpa terulang.
- **Visi & Misi:** visi tampil sebagai kutipan pada kartu navy berlabel **Visi
  Utama** dengan slogan organisasi sebagai penutup, sedangkan misi tampil
  sebagai daftar bernomor pada kartu putih.
- **Bidang Pengabdian Utama:** kartu diambil dari **Program → Kategori
  Program** — kepala kartu memakai warna kategori, keterangannya berisi jumlah
  program, dan tautannya menuju daftar program kategori tersebut. Menambah
  kategori otomatis menambah kartu, tanpa mengubah kode.
- **Tokoh Organisasi:** kartu putih bersudut tumpul dan berbayang halus
  (`.card .figure-card`) menampilkan foto penuh selebar kartu pada bagian atas
  (rasio 4:5), lalu nama dan jabatan para pendiri di bawahnya; foto dapat diklik
  untuk pratinjau. Empat kartu sejajar pada layar lebar, dua kolom di tablet,
  dan satu kolom di ponsel. Data diambil dari **Tentang → Tokoh Organisasi**
  menurut kolom Urutan; tokoh nonaktif tidak dirender dan bagian disembunyikan
  bila tidak ada tokoh aktif. Foto kosong diganti bidang inisial nama. Dijaga
  oleh `Tests\Feature\OrganizationFigureTest`.
- **Struktur Pengurus:** dikelompokkan per tingkat (DPP/DPW/PC) dan dipilih
  lewat tab. Ketua tiap tingkat tampil lebih besar bersama masa bakti dan
  kutipannya (`bio`), sedangkan pengurus lain tampil sebagai kartu sejajar;
  pengurus tanpa foto memakai bulatan inisial berwarna palet situs. Tab memakai
  tombol `.chip` dengan penanda `.is-active` (sama seperti penyaring pada
  halaman daftar) dan perilakunya ditambahkan bertahap lewat `[data-tabs]` pada
  `resources/js/app.js`: tanpa JavaScript seluruh tingkat tetap tampil lengkap,
  dan tombol panah kiri/kanan dapat dipakai berpindah tab. Bagian ini memakai
  latar halaman yang sama dengan bagian **Bidang Pengabdian Utama** (tanpa pita
  `.section-soft`), sehingga menyatu dengan bagian di sekitarnya.
- **Sejarah:** kronologi peristiwa yang dipilih lewat tab tahun — tombol
  `.chip` dengan titik gold pada tab aktif, pola yang sama seperti Struktur
  Pengurus. Setiap peristiwa tampil sebagai satu kartu: lencana tahun &
  lokasi, judul, deskripsi, baris lokasi berpeta, dan foto pada panel navy
  (`Tentang → Sejarah`: tahun, judul, deskripsi, lokasi, gambar).
- **CTA:** kartu ajakan **Ingin tahu lebih banyak?** (navy) kini diikuti bagian
  Identitas Organisasi, bukan langsung footer. Padding atasnya tetap
  `pt-20 md:pt-25` sehingga jarak dari bagian sebelumnya 10rem/12.5rem,
  sedangkan padding bawahnya satu langkah `.section-y` (`pb-20 md:pb-25`);
  bagian berikutnya menambahkan padding atasnya sendiri sehingga jarak
  antarbagian tetap 10rem/12.5rem tanpa celah berlebih. Dijaga oleh
  `Tests\Feature\PublicPagesTest::test_halaman_tentang_tanpa_mitra_dan_cta_diikuti_identitas_organisasi`.
- **Identitas Organisasi:** bagian penutup berlatarkan `.section-soft` yang sama
  dengan Sejarah, memakai label `.section-eyebrow` dan judul `.section-title`.
  Teks isinya dibatasi `max-w-4xl` (896px) dan rata tengah; lirik 16px di
  ponsel dan 18px di desktop (`whitespace-pre-line`) dengan jarak antarbait
  `space-y-8`. Judul dan lirik disunting lewat **Tentang → Identitas
  Organisasi**; baris pertama `Ciptaan: ...` tampil sebagai kredit. Gambar
  pendukung (mis. lembar partitur) tampil di bawah lirik pada wadah
  `max-w-5xl` — satu gambar terpusat lebih besar, beberapa gambar dua kolom di
  layar lebar, rasio asli dipertahankan tanpa crop, dan setiap gambar membuka
  lightbox situs dalam satu grup. Bagian tetap tampil bila hanya ada gambar.
  Dijaga oleh `Tests\Feature\OrganizationIdentityTest`.

## Aset Front-end: Kenapa Lokal Bisa Berbeda dari Hosting

Kelas Tailwind pada berkas Blade tidak dibaca saat halaman dibuka. Semuanya
dikompilasi lebih dahulu oleh Vite menjadi satu berkas CSS beralamat isi
(`public/build/assets/app-<hash>.css`), dan `@vite(...)` pada
`resources/views/layouts/app.blade.php` mengambil nama berkas itu dari
`public/build/manifest.json`. Artinya: **kelas baru yang belum ada di dalam
bundel CSS tidak berpengaruh apa pun**, meski sudah ditulis di Blade.

Direktori `/public/build` sengaja diabaikan Git (`.gitignore` baris 16),
sehingga `git push` **tidak pernah** membawa CSS. Hosting selalu memakai bundel
terakhir yang pernah diunggah ke sana. Gejala yang pernah terjadi karena hal
ini: hero beranda kehilangan padding bawah (`pb-20`) sehingga titik indikator
carousel (`.hero-bg-dots`, dipatok `bottom: 2.5rem`) tampak menumpuk pada tombol
**Jelajahi Program**, jarak atas sebelum baris *eyebrow* hilang (`pt-12`), dan
panah dropdown formulir (`select.field-input`) kembali menempel ke tepi kanan
kotak karena peramban memakai bundel lama tanpa chevron SVG dari
`resources/css/app.css`.

Cara memastikan hosting memakai bundel yang sama dengan lokal:

```bash
# 1. Lokal: bangun ulang lalu lihat nama berkas CSS-nya
npm run build
cat public/build/manifest.json          # -> assets/app-XXXXXXXX.css

# 2. Hosting: berkas CSS yang dirujuk halaman harus sama
curl -s https://CONTOH-DOMAIN/ | grep -o 'assets/app-[^"]*\.css'

# 3. Hosting: aturan baru harus ada di bundel yang dipakai
grep -c 'pb-20{' public/build/assets/app-*.css     # hasil minimal 1
grep -c 'select\.field-input{' public/build/assets/app-*.css   # chevron dropdown
```

Menyusulkan bundel (pilih salah satu):

- **Bangun di hosting** (bila Node tersedia):
  `git pull && npm ci && npm run build && php artisan optimize:clear`.
- **Unggah bundel** (hosting tanpa Node): bangun di lokal, lalu kirim seluruh
  `public/build` — atau pakai bundel yang sudah disiapkan pada
  `public/build.zip` (isi: `build/manifest.json`, `build/assets/*`):
  `cd public && unzip -o build.zip && php artisan view:clear`. Ubah isi zip
  dengan `cd public && rm -f build.zip && zip -r build.zip build -x '__MACOSX/*'`
  (jangan pakai *Compress* Finder — hasilnya menambah entri `__MACOSX/`).
- **Ikutkan lewat Git**: hapus `/public/build` dari `.gitignore` lalu
  `git add -f public/build`, dengan catatan setiap perubahan kelas Tailwind
  wajib diikuti `npm run build` sebelum `git commit`.

Setelah bundel baru terpasang, nama berkas CSS ikut berubah sehingga peramban
otomatis memuat berkas baru (tidak perlu *hard refresh*). Perubahan pada
`resources/js/app.js` memakai nama berkas JS terpisah (`app-<hash>.js`), jadi
ubah CSS saja tidak mengubah nama berkas JS.

Jalankan `php artisan view:clear` **sebelum** `npm run build`. Berkas
`resources/css/app.css` (baris 4) ikut memindai
`storage/framework/views/*.php`, sehingga sisa tampilan terkompilasi — termasuk
milik panel Filament — turut masuk bundel. Tanpa `view:clear`, bundel pernah
membengkak dari ±74 kB menjadi ±89 kB tanpa satu pun kelas yang benar-benar
dipakai halaman publik; hasil build setelah `view:clear` deterministik karena
hanya membaca `resources/views` dan `resources/js`.

## Keamanan & Privasi

Bagian ini merangkum pengamanan yang dipasang di kode aplikasi (bukan hanya di
web server), hasil tindak lanjut audit kode.

### Kata sandi admin

Tidak ada fallback kata sandi pada `config/perwama.php`. `AdminUserSeeder`
menolak berjalan di luar `local`/`testing` bila `ADMIN_PASSWORD` kosong, kurang
dari 12 karakter, atau termasuk kata sandi yang mudah ditebak. Uji:
`Tests\Feature\AdminUserSeederSecurityTest`.

### Formulir publik (kontak, pendaftaran, telemetri)

Endpoint tulis tanpa login dibatasi per alamat IP lewat `RateLimiter` pada
`AppServiceProvider` dan middleware `throttle:` di `routes/web.php`:

| Endpoint | Batas |
| --- | --- |
| `POST /kontak` | 5 kali / 10 menit, 25 kali / hari |
| `POST /bergabung`, `POST /bergabung/mitra` | 5 kali / jam, 15 kali / hari |
| `POST /jejak-kunjungan` | 60 kali / menit |
| `GET /sesi/segar` | 15 kali / menit |

Selain itu, kedua formulir memuat `resources/views/partials/honeypot.blade.php`:
kolom `website` yang tidak terlihat dan tidak dapat difokuskan. Bila terisi,
pengirim tetap menerima pesan sukses yang sama, tetapi tidak ada baris yang
disimpan (`App\Support\Honeypot`). Di belakang proxy/CDN, isi `TRUSTED_PROXIES`
agar pembatasan memakai alamat pengunjung sebenarnya — tanpa nilai itu header
`X-Forwarded-For` tidak dipercaya sehingga batas tidak dapat dilewati dengan
memalsukannya.

Halaman **Bergabung** juga memuat formulir permohonan kerja sama
(`#mitra`, `POST /bergabung/mitra`). Isian nama, email, jenis kerja sama
(mitra/kolaborasi), dan uraian wajib diisi; permohonan tersimpan di tabel
`partnership_requests` dengan status `pending` dan ditinjau melalui panel
**Permohonan Kerja Sama**.

Uji: `Tests\Feature\PublicFormProtectionTest`.

### Pembersihan HTML (RichEditor)

Isi berita, program, kegiatan, cerita, video, deskripsi wilayah, dan deskripsi
lembaga dirender sebagai HTML. Semuanya dibersihkan lebih dulu oleh
`App\Support\HtmlSanitizer` (allowlist tag/atribut) lewat helper `html_aman()`;
kolom `map_embed` memakai `peta_embed()` yang hanya mempertahankan iframe peta
Google/OpenStreetMap. Tag berbahaya (`script`, `style`, `object`, `iframe` di
konten biasa) dibuang beserta isinya, atribut `on*` dan skema `javascript:`
ditolak, dan tautan `target="_blank"` selalu mendapat
`rel="noopener noreferrer"`. Uji: `Tests\Feature\HtmlSanitizationTest`.

Karena pembersihan terjadi saat render, data lama atau hasil impor ikut aman
tanpa perlu migrasi ulang.

### Publikasi agenda

`App\Models\Event` kini memiliki scope `published()` seperti News/Program/
Activity/Story/Video. Agenda dengan `published_at` kosong atau bertanggal masa
depan tidak muncul di `/kegiatan` dan menghasilkan 404 bila slug-nya dibuka
langsung. Pastikan **Tanggal Terbit** terisi pada setiap agenda yang ingin
tampil (form admin sudah mengisinya dengan waktu sekarang secara bawaan). Uji:
`Tests\Feature\EventPublicationTest`.

### Header keamanan & cookie

`App\Http\Middleware\SecurityHeaders` dipasang untuk semua respons dan menambah
`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`,
`Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, serta
CSP terbatas (`frame-ancestors`, `base-uri`, `object-src`, `form-action`) yang
tidak membatasi skrip/gaya sehingga panel Filament tetap berjalan. HSTS tetap
menjadi urusan web server karena bergantung pada sertifikat/domain.

`config/session.php` membuat cookie sesi otomatis `Secure` bila `APP_URL`
memakai `https://`; `SESSION_SECURE_COOKIE` dapat menimpanya bila perlu. Uji:
`Tests\Feature\SecurityHeadersTest`.

### Log Aktivitas

`App\Observers\AuditObserver` mencatat create/update/delete yang dilakukan
pengguna yang login ke tabel `audit_logs` (dibaca pada menu **Pengaturan → Log
Aktivitas**, kini hanya baca). Kata sandi, token, stempel waktu, dan data
pribadi sensitif (NIK, alamat, tanggal/tempat lahir) tidak ikut tersalin. Uji:
`Tests\Feature\AuditLogTest`.

### Enkripsi NIK & retensi data pribadi (SEC-04)

NIK pada `membership_applications` dan `members` disimpan **terenkripsi**
(AES-256 lewat `APP_KEY`) memakai cast `App\Casts\EncryptedString`. Data lama
yang masih berupa teks biasa tetap dapat dibaca dan akan ikut terenkripsi saat
record disimpan kembali. Karena kolom terenkripsi tidak bisa dicari dengan
`LIKE`, tabel panel **Pendaftaran Anggota** dan **Anggota** menampilkan NIK
bertopeng (`App\Models\Concerns\MasksNik`, mis. `••••••••••••1234`); formulir
memakai kolom kata sandi yang dapat dibuka dengan ikon mata. NIK tidak pernah
disalin ke Log Aktivitas.

Retensi dijalankan otomatis tiap hari pukul 02.00 oleh
`php artisan membership:purge-rejected` (jadwal di `routes/console.php`):
pendaftaran berstatus **ditolak** yang telah melewati masa retensi dianonimkan
— nama diganti keterangan anonim, NIK/alamat/tanggal lahir/kontak/motivasi/
catatan admin dikosongkan — sementara baris dan statusnya tetap ada untuk
statistik. Masa retensi bawaan **180 hari** sejak `reviewed_at` (atau
`updated_at` bila `reviewed_at` kosong) dan dapat diubah lewat
`MEMBERSHIP_RETENTION_DAYS` atau `config/perwama.php`. Perintah juga dapat
dijalankan manual dengan `--days=N` untuk penyesuaian sekali jalan.

**PENTING:** `APP_KEY` adalah kunci dekripsi NIK. Simpan cadangannya secara
aman; kehilangan `APP_KEY` membuat NIK lama tidak dapat dibaca. Mengganti
`APP_KEY` tanpa mendekripsi data lebih dulu akan membuat NIK tidak terbaca.

Yang masih perlu diputuskan organisasi: masa retensi data **anggota aktif/
nonaktif** dan pesan kontak (perintah ini hanya menangani pendaftaran yang
ditolak), serta apakah `email`/`whatsapp` pendaftar perlu ikut dianonimkan
lebih cepat. Uji: `Tests\Feature\PersonalDataProtectionTest`.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
