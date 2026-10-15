# Foto Konten Asli PERWAMA INA

Letakkan **foto kegiatan asli PERWAMA INA** di folder ini, lalu jalankan ulang seeder:

```bash
php artisan db:seed --class=FrontendSeeder --force
```

Seeder memakai foto di folder ini secara otomatis **bila berkasnya ada**, dan
memakai foto cadangan (bila belum ada). Format nama berkas mengikuti kunci
foto pada seeder, contoh:

| Bagian | Pola nama berkas |
| --- | --- |
| Kegiatan (daftar kegiatan ke-n, mulai 0) | `kegiatan-0.jpg`, `kegiatan-1.jpg`, … |
| Agenda/event ke-n (mulai 1) | `agenda-1.jpg`, `agenda-2.jpg`, … |
| Album galeri ke-n (mulai 0) | `album-0.jpg`, … |
| Isi album | `album-0-0.jpg`, `album-0-1.jpg`, … |
| Berita ke-n (mulai 0) | `berita-0.jpg`, … |
| Kisah / foto tokoh | `kisah-0.jpg`, `tokoh-0.jpg` |
| Program (kategori + indeks) | `program-pendidikan-0.jpg`, `program-ekonomi-1.jpg`, … |
| Video ke-n (mulai 0) | `video-0.jpg` |
| Wilayah / organisasi | `wilayah-maluku.jpg`, `wilayah-dki-jakarta.jpg` |
| Logo | `logo.png`, `logo-white.png` |

Ekstensi yang dikenali: `.jpg`, `.jpeg`, `.png`, `.webp`.
Rasio yang dipakai kartu: **4:3** (kartu grid), **3:2** (kartu unggulan),
**4:5** (foto hero). Sebaiknya unggah foto minimal 1200 px pada sisi terpanjang
agar tetap tajam di layar besar.
