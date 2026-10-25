<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\AlbumCategory;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Management;
use App\Models\Member;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationFigure;
use App\Models\OrganizationHistory;
use App\Models\OrganizationProfile;
use App\Models\OrganizationValue;
use App\Models\Partner;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Publication;
use App\Models\Region;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mengisi seluruh konten situs publik PERWAMA INA.
 *
 * Seeder ini idempoten: slug dan `key` unik dipakai sebagai acuan
 * `updateOrCreate`, sehingga aman dijalankan berulang kali.
 */
class FrontendSeeder extends Seeder
{
    /** Alamat kantor pusat yang dipakai pada beberapa bagian. */
    private string $city = 'Jakarta Selatan';

    public function run(): void
    {
        $this->seedProfile();
        $this->seedHistories();
        $this->seedFigures();
        $this->seedValues();
        $this->seedStatistics();
        $this->seedRegions();
        $this->seedOrganizations();
        $this->seedManagements();
        $this->seedPrograms();
        $this->seedEventsAndActivities();
        $this->seedGalleries();
        $this->seedVideos();
        $this->seedNewsAndStories();
        $this->seedPublications();
        $this->seedPartners();
        $this->seedMembers();
        $this->seedTags();

        $this->command->info('Konten situs publik PERWAMA INA berhasil diisi.');
    }

    /**
     * Foto konten.
     *
     * Foto kegiatan asli PERWAMA dipakai bila tersedia: letakkan berkas pada
     * `public/images/konten/{seed}.jpg|jpeg|png|webp` (mis.
     * `public/images/konten/kegiatan-1.jpg`) dan seeder akan memakainya.
     * Bila belum ada, seeder memakai foto cadangan agar halaman tetap lengkap.
     */
    private function img(string $seed, int $width = 1200, int $height = 800): string
    {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            $relative = 'images/konten/'.$seed.'.'.$extension;

            if (is_file(public_path($relative))) {
                return $relative;
            }
        }

        return 'https://picsum.photos/seed/perwama-'.$seed.'/'.$width.'/'.$height;
    }

    private function seedProfile(): void
    {
        OrganizationProfile::query()->updateOrCreate(['id' => 1], [
            'name' => 'Persatuan Wanita Maluku Indonesia',
            'abbreviation' => 'PERWAMA INA',
            'tagline' => 'Perempuan Maluku untuk Indonesia',
            'slogan' => 'Perempuan Maluku Bersatu, Berdaya, dan Berkarya untuk Indonesia',
            'vision' => 'Menjadi organisasi perempuan Maluku yang tangguh, mandiri, dan berdaya saing di tingkat nasional pada tahun 2035.',
            'mission' => [
                'Menghimpun dan mempererat persaudaraan perempuan Maluku di seluruh Indonesia.',
                'Meningkatkan kapasitas perempuan Maluku melalui pendidikan, pelatihan, dan literasi.',
                'Mendorong kemandirian ekonomi perempuan melalui usaha produktif dan koperasi.',
                'Memperjuangkan perlindungan perempuan dan anak serta kesetaraan gender.',
                'Melestarikan budaya, bahasa, dan kearifan lokal Maluku.',
            ],
            'short_description' => 'PERWAMA INA menghimpun, memberdayakan, dan memperkuat peran perempuan Maluku di seluruh Indonesia melalui pendidikan, kesehatan, ekonomi, dan pelestarian budaya.',
            'description' => '<p>Persatuan Wanita Maluku — Indonesia (PERWAMA INA) adalah organisasi kemasyarakatan perempuan yang menghimpun perempuan Maluku, baik yang berdomisili di Maluku dan Maluku Utara maupun yang merantau di berbagai provinsi Indonesia.</p><p>Sejak berdiri, PERWAMA INA bergerak pada bidang pendidikan, kesehatan, ekonomi produktif, sosial kemanusiaan, hukum dan hak asasi manusia, serta pelestarian budaya.</p><p>Melalui jaringan Dewan Pengurus Pusat, Dewan Pengurus Wilayah, dan Pengurus Cabang, kami mendampingi perempuan Maluku agar berdaya, mandiri, dan mampu berkontribusi nyata bagi keluarga serta masyarakat.</p>',
            'address' => 'Jl. Pangeran Antasari No. 45, Cipete Selatan',
            'city' => $this->city,
            'province' => 'DKI Jakarta',
            'postal_code' => '12410',
            'email' => 'sekretariat@perwama-ina.or.id',
            'phone' => '(021) 7591-2255',
            'whatsapp' => '0812-9000-1122',
            'founded_year' => 1998,
            'logo' => $this->img('logo', 400, 400),
            'logo_white' => $this->img('logo-white', 400, 400),
            'facebook' => 'https://facebook.com/perwamaina',
            'instagram' => 'https://instagram.com/perwamaina',
            'youtube' => 'https://youtube.com/@perwamaina',
            'twitter' => 'https://x.com/perwamaina',
            'tiktok' => 'https://tiktok.com/@perwamaina',
            'map_embed' => '<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=106.78%2C-6.29%2C106.82%2C-6.25&amp;layer=mapnik" style="border:0;width:100%;height:320px;" loading="lazy" title="Peta kantor PERWAMA INA"></iframe>',
        ]);
    }

    private function seedHistories(): void
    {
        $histories = [
            ['year' => '1998', 'title' => 'Berdirinya PERWAMA INA', 'location' => 'Jakarta Selatan', 'description' => 'Diawali pertemuan para perempuan Maluku di Jakarta, PERWAMA INA lahir sebagai wadah silaturahmi dan kebersamaan keluarga besar Maluku.'],
            ['year' => '2005', 'title' => 'Penguatan Kelembagaan', 'location' => 'Ambon', 'description' => 'Pembentukan Dewan Pengurus Wilayah di Maluku dan Maluku Utara serta penyusunan anggaran dasar dan rumah tangga organisasi.'],
            ['year' => '2012', 'title' => 'Gerakan Literasi Perempuan', 'location' => 'Maluku Tengah', 'description' => 'Program pendidikan dan literasi digital mulai menjangkau desa-desa di Maluku Tengah, Maluku Tenggara, dan Pulau Buru.'],
            ['year' => '2019', 'title' => 'Kemandirian Ekonomi', 'location' => 'Ambon', 'description' => 'Pendirian koperasi wanita dan pelatihan usaha produktif berbasis hasil laut, sagu, serta tenun Maluku.'],
            ['year' => '2024', 'title' => 'Jaringan Nasional', 'location' => 'Jakarta', 'description' => 'PERWAMA INA hadir di 18 provinsi melalui pengurus cabang, dengan fokus advokasi perlindungan perempuan dan anak.'],
        ];

        foreach ($histories as $index => $history) {
            OrganizationHistory::query()->updateOrCreate(
                ['title' => $history['title']],
                $history + [
                    'image' => $this->img('sejarah-'.$index),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * Pendiri yang tampil pada bagian "Tokoh Organisasi".
     *
     * Tiga nama di bawah berasal dari data pendiri PERWAMA INA yang tercatat.
     * Kartu keempat dapat ditambahkan lewat panel Tentang → Tokoh Organisasi
     * (tata letak sudah menyediakan empat kolom pada layar lebar). Foto sengaja
     * tidak diisi contoh acak karena wajah tokoh harus berasal dari unggahan
     * asli pengelola; selama belum diunggah, kartu menampilkan inisial nama.
     * `photo` juga tidak ikut ditulis saat seeder dijalankan ulang agar foto
     * yang sudah diunggah tidak terhapus.
     */
    private function seedFigures(): void
    {
        $figures = [
            'Robine Inka Caecilia M. P.',
            'Johanna Pietersz',
            'Evie Poetiray',
        ];

        foreach ($figures as $index => $name) {
            OrganizationFigure::query()->updateOrCreate(
                ['name' => $name],
                [
                    'position' => 'Pendiri',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedValues(): void
    {
        $values = [
            ['title' => 'Solidaritas', 'description' => 'Kami menempatkan kebersamaan dan gotong royong sebagai kekuatan utama dalam setiap gerakan. Persaudaraan perempuan Maluku — baik yang di kampung halaman maupun di perantauan — menjadi energi yang menjaga organisasi tetap tumbuh.', 'color' => '#1B3A63'],
            ['title' => 'Pemberdayaan', 'description' => 'Setiap perempuan berhak memiliki ruang untuk tumbuh, berkarya, dan menentukan masa depannya. Kami mendampingi anggota melalui pelatihan keterampilan, literasi, dan pendampingan usaha agar mandiri secara ekonomi.', 'color' => '#C4622D'],
            ['title' => 'Kesetaraan', 'description' => 'Kami memperjuangkan kesetaraan gender serta perlindungan perempuan dan anak di semua lini. Advokasi, pendampingan korban, dan pendidikan publik kami jalankan bersama mitra layanan dan penegak hukum.', 'color' => '#2F7A5B'],
            ['title' => 'Budaya', 'description' => 'Kami merawat bahasa, adat, dan kearifan lokal Maluku sebagai identitas generasi berikutnya. Sanggar budaya, kelas bahasa daerah, dan festival tahunan menjadi cara kami menjaga warisan itu tetap hidup.', 'color' => '#B8860B'],
            ['title' => 'Integritas', 'description' => 'Tata kelola organisasi kami jalankan dengan transparan, akuntabel, dan penuh tanggung jawab. Setiap program dilaporkan secara terbuka kepada anggota serta mitra yang mempercayakan dukungannya.', 'color' => '#6B3FA0'],
        ];

        foreach ($values as $index => $value) {
            OrganizationValue::query()->updateOrCreate(
                ['title' => $value['title']],
                $value + ['icon' => 'heroicon-o-sparkles', 'sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }

    private function seedStatistics(): void
    {
        $statistics = [
            ['key' => 'anggota', 'label' => 'Anggota Terdaftar', 'value' => '12.480', 'suffix' => '+'],
            ['key' => 'provinsi', 'label' => 'Provinsi Jangkauan', 'value' => '18', 'suffix' => ''],
            ['key' => 'program', 'label' => 'Program Berjalan', 'value' => '36', 'suffix' => ''],
            ['key' => 'kegiatan', 'label' => 'Kegiatan Terlaksana', 'value' => '540', 'suffix' => '+'],
            ['key' => 'pengurus_cabang', 'label' => 'Pengurus Cabang', 'value' => '64', 'suffix' => ''],
            ['key' => 'penerima_manfaat', 'label' => 'Penerima Manfaat', 'value' => '25.000', 'suffix' => '+'],
        ];

        foreach ($statistics as $index => $statistic) {
            Statistic::query()->updateOrCreate(
                ['key' => $statistic['key']],
                $statistic + ['icon' => 'heroicon-o-users', 'sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }

    /**
     * Provinsi dan kabupaten/kota sebagai wilayah kerja organisasi.
     */
    private function seedRegions(): void
    {
        $provinces = [
            'Maluku' => ['Maluku', 'Provinsi kepulauan di Maluku dengan ibu kota Ambon, pusat gerakan PERWAMA INA di tanah asal.', -3.2385, 130.1453],
            'Maluku Utara' => ['Maluku', 'Wilayah kepulauan di utara Maluku dengan ibu kota Sofifi dan kota Ternate sebagai simpul budaya.', 1.5709, 127.8088],
            'DKI Jakarta' => ['Jawa', 'Pusat pemerintahan nasional dan kantor Sekretariat Nasional PERWAMA INA.', -6.2088, 106.8456],
            'Jawa Barat' => ['Jawa', 'Provinsi dengan komunitas keluarga Maluku yang besar di Bandung dan Bekasi.', -6.9147, 107.6098],
            'Banten' => ['Jawa', 'Kawasan penyangga ibu kota dengan banyak komunitas perempuan Maluku.', -6.1783, 106.4019],
            'Jawa Tengah' => ['Jawa', 'Wilayah dengan komunitas diaspora Maluku di Semarang dan Surakarta.', -6.9932, 110.4203],
            'DI Yogyakarta' => ['Jawa', 'Kota pelajar dengan banyak mahasiswa asal Maluku dan kegiatan literasi.', -7.7956, 110.3695],
            'Jawa Timur' => ['Jawa', 'Basis komunitas Maluku di Surabaya, Malang, dan sekitarnya.', -7.5361, 112.2384],
            'Bali' => ['Bali & Nusa Tenggara', 'Wilayah dengan banyak pekerja perempuan Maluku pada sektor pariwisata.', -8.4095, 115.1889],
            'Nusa Tenggara Timur' => ['Bali & Nusa Tenggara', 'Wilayah kepulauan dengan ikatan budaya Maluku melalui Maluku Tenggara.', -8.6574, 121.0794],
            'Sulawesi Utara' => ['Sulawesi', 'Wilayah tetangga dengan kedekatan budaya masyarakat Maluku di Bitung dan Manado.', 1.4748, 124.8421],
            'Sulawesi Selatan' => ['Sulawesi', 'Basis diaspora Maluku di Makassar dengan kegiatan ekonomi produktif perempuan.', -3.6687, 119.9741],
            'Kalimantan Timur' => ['Kalimantan', 'Wilayah dengan komunitas Maluku di Balikpapan dan Samarinda.', -0.5022, 117.1536],
            'Kalimantan Selatan' => ['Kalimantan', 'Wilayah dengan komunitas keluarga Maluku di Banjarmasin.', -3.3186, 114.5944],
            'Sumatera Utara' => ['Sumatera', 'Basis diaspora Maluku di Medan dengan kegiatan sosial keagamaan.', 3.5915, 98.6722],
            'Riau' => ['Sumatera', 'Wilayah dengan komunitas Maluku di Pekanbaru dan Kepulauan Riau.', 0.5071, 101.4478],
            'Papua' => ['Papua', 'Wilayah paling timur dengan kedekatan budaya Kepulauan Aru dan Maluku Tenggara.', -2.5337, 140.7181],
            'Papua Barat' => ['Papua', 'Wilayah dengan komunitas Maluku di Sorong dan Raja Ampat.', -1.3361, 133.1747],
        ];

        $order = 0;

        foreach ($provinces as $name => [$island, $description, $lat, $lon]) {
            $order++;

            Region::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'parent_id' => null,
                    'type' => 'province',
                    'province' => $name,
                    'island' => $island,
                    'description' => $description,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'cover_image' => $this->img('wilayah-'.Str::slug($name)),
                    'sort_order' => $order,
                    'is_active' => true,
                ],
            );
        }

        $this->seedRegencies();
    }

    /**
     * Kabupaten/kota di Maluku dan Maluku Utara.
     */
    private function seedRegencies(): void
    {
        $regencies = [
            'Maluku' => [
                'Kota Ambon' => ['city', 'Ibu kota Provinsi Maluku dan sekretariat DPW Maluku.', -3.6954, 128.1814],
                'Kota Tual' => ['city', 'Kota di Maluku Tenggara dengan tradisi tenun dan perikanan.', -5.6325, 132.7487],
                'Kabupaten Maluku Tengah' => ['regency', 'Wilayah Pulau Seram dan Ambon dengan program ekonomi sagu.', -3.2911, 128.7859],
                'Kabupaten Maluku Tenggara' => ['regency', 'Wilayah Kei Kecil dan Kei Besar dengan pelestarian budaya.', -5.6944, 132.8217],
                'Kabupaten Seram Bagian Barat' => ['regency', 'Wilayah pengembangan usaha hasil laut dan tenun.', -3.3043, 128.3485],
                'Kabupaten Seram Bagian Timur' => ['regency', 'Wilayah pesisir Seram Timur dengan program literasi.', -3.2209, 130.7139],
                'Kabupaten Buru' => ['regency', 'Wilayah Pulau Buru dengan program pertanian dan kesehatan.', -3.3175, 126.6833],
                'Kabupaten Buru Selatan' => ['regency', 'Wilayah pemekaran Pulau Buru dengan penguatan kelompok tani.', -3.6417, 126.7606],
                'Kabupaten Kepulauan Aru' => ['regency', 'Wilayah kepulauan dengan pelestarian budaya dan perikanan.', -6.1708, 134.5093],
                'Kabupaten Maluku Barat Daya' => ['regency', 'Wilayah pulau-pulau kecil dengan program air bersih.', -7.7816, 127.9435],
            ],
            'Maluku Utara' => [
                'Kota Ternate' => ['city', 'Kota rempah dengan sekretariat DPW Maluku Utara.', 0.7966, 127.3849],
                'Kota Tidore Kepulauan' => ['city', 'Wilayah warisan rempah dan pelestarian adat.', 0.6977, 127.4616],
                'Kabupaten Halmahera Barat' => ['regency', 'Wilayah Jailolo dengan program pertanian dan budaya.', 1.0613, 127.4929],
                'Kabupaten Halmahera Tengah' => ['regency', 'Wilayah Weda dengan program pemberdayaan ekonomi.', 0.5397, 128.5315],
                'Kabupaten Halmahera Utara' => ['regency', 'Wilayah Tobelo dengan program pendidikan perempuan.', 1.5249, 128.0211],
                'Kabupaten Halmahera Selatan' => ['regency', 'Wilayah Labuha dengan pelestarian bahasa daerah.', -0.5554, 127.4971],
                'Kabupaten Halmahera Timur' => ['regency', 'Wilayah Maba dengan program kesehatan ibu dan anak.', 0.6032, 128.4931],
                'Kabupaten Kepulauan Sula' => ['regency', 'Wilayah Sanana dengan program usaha hasil laut.', -2.2325, 125.9701],
                'Kabupaten Pulau Morotai' => ['regency', 'Wilayah pulau dengan program pariwisata berbasis masyarakat.', 2.1562, 128.4345],
                'Kabupaten Pulau Taliabu' => ['regency', 'Wilayah paling barat Maluku Utara dengan penguatan kelompok wanita.', -1.8454, 124.9823],
            ],
        ];

        foreach ($regencies as $provinceName => $items) {
            $province = Region::query()->where('slug', Str::slug($provinceName))->first();

            if (! $province) {
                continue;
            }

            $order = 0;

            foreach ($items as $name => [$type, $description, $lat, $lon]) {
                $order++;

                Region::query()->updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'name' => $name,
                        'parent_id' => $province->getKey(),
                        'type' => $type,
                        'province' => $provinceName,
                        'island' => $province->island,
                        'description' => $description,
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'cover_image' => $this->img('wilayah-'.Str::slug($name)),
                        'sort_order' => $order,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /**
     * Struktur organisasi: DPP dan DPW.
     */
    private function seedOrganizations(): void
    {
        $dpp = Organization::query()->updateOrCreate(
            ['slug' => 'dpp-perwama-ina'],
            [
                'region_id' => $this->regionId('DKI Jakarta'),
                'parent_id' => null,
                'name' => 'Dewan Pengurus Pusat PERWAMA INA',
                'type' => 'dpp',
                'leader_name' => 'Ny. Rosalina Melatunan, S.E.',
                'secretary_name' => 'Ny. Damaris Siahaya, S.H.',
                'address' => 'Jl. Pangeran Antasari No. 45, Cipete Selatan, Jakarta Selatan',
                'phone' => '(021) 7591-2255',
                'email' => 'dpp@perwama-ina.or.id',
                'whatsapp' => '0812-9000-1122',
                'facebook' => 'https://facebook.com/perwamaina',
                'instagram' => 'https://instagram.com/perwamaina',
                'youtube' => 'https://youtube.com/@perwamaina',
                'logo' => $this->img('logo', 400, 400),
                'cover_image' => $this->img('dpp'),
                'description' => 'Dewan Pengurus Pusat merupakan penanggung jawab kebijakan organisasi di tingkat nasional.',
                'founded_at' => '1998-08-17',
                'member_count' => 12480,
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        $wilayah = [
            ['Maluku', 'Maluku', 'Ny. Juliana Tuhuteru', 'Ny. Sandra Tuasikal', 2140],
            ['Maluku Utara', 'Maluku Utara', 'Ny. Fatimah Rahman', 'Ny. Marlia Usman', 1630],
            ['DKI Jakarta', 'DKI Jakarta', 'Ny. Cornelia Pattiasina', 'Ny. Elisabeth Rumahloine', 2480],
            ['Jawa Barat', 'Jawa Barat', 'Ny. Yuliana Sahuburua', 'Ny. Grace Maahury', 1520],
            ['Sulawesi Selatan', 'Sulawesi Selatan', 'Ny. Nurhayati Latuconsina', 'Ny. Rahmawati Sanaky', 980],
            ['Papua', 'Papua', 'Ny. Selviana Kaimudin', 'Ny. Yohana Wenda', 640],
        ];

        $order = 0;

        foreach ($wilayah as [$regionName, $suffix, $leader, $secretary, $members]) {
            $order++;

            Organization::query()->updateOrCreate(
                ['slug' => 'dpw-perwama-ina-'.Str::slug($suffix)],
                [
                    'region_id' => $this->regionId($regionName),
                    'parent_id' => $dpp->getKey(),
                    'name' => 'Dewan Pengurus Wilayah '.$suffix,
                    'type' => 'dpw',
                    'leader_name' => $leader,
                    'secretary_name' => $secretary,
                    'address' => 'Sekretariat DPW '.$suffix,
                    'email' => 'dpw.'.Str::slug($suffix).'@perwama-ina.or.id',
                    'cover_image' => $this->img('dpw-'.Str::slug($suffix)),
                    'description' => 'Dewan Pengurus Wilayah mengoordinasikan pengurus cabang dan kegiatan di tingkat provinsi '.$suffix.'.',
                    'founded_at' => '2005-05-02',
                    'member_count' => $members,
                    'sort_order' => $order,
                    'is_active' => true,
                ],
            );
        }

        $this->seedBranches();
    }

    /**
     * Pengurus cabang dan ranting sebagai ujung gerakan di daerah.
     */
    private function seedBranches(): void
    {
        $cabang = [
            ['Kota Ambon', 'Maluku', 'Ny. Helena Rehatta', 420],
            ['Kota Tual', 'Maluku', 'Ny. Marthina Renyaan', 260],
            ['Kabupaten Buru', 'Maluku', 'Ny. Yosepha Wally', 310],
            ['Kota Ternate', 'Maluku Utara', 'Ny. Aisyah Umar', 340],
            ['Kota Tidore Kepulauan', 'Maluku Utara', 'Ny. Hasnah Suleman', 210],
            ['Kabupaten Halmahera Utara', 'Maluku Utara', 'Ny. Ribka Tapiheru', 180],
        ];

        $order = 0;
        $provinceBranchCount = [];

        foreach ($cabang as [$regionName, $provinceName, $leader, $members]) {
            $order++;

            $branchIndex = $provinceBranchCount[$provinceName] ?? 0;
            $provinceBranchCount[$provinceName] = $branchIndex + 1;

            $pc = Organization::query()->updateOrCreate(
                ['slug' => 'pc-perwama-ina-'.Str::slug($regionName)],
                [
                    'region_id' => $this->regionId($regionName),
                    'parent_id' => Organization::query()
                        ->where('slug', 'dpw-perwama-ina-'.Str::slug($provinceName))
                        ->value('id'),
                    'name' => 'Pengurus Cabang '.$regionName,
                    'type' => 'pc',
                    'leader_name' => $leader,
                    'address' => 'Sekretariat PC '.$regionName,
                    'email' => 'pc.'.Str::slug($regionName).'@perwama-ina.or.id',
                    'cover_image' => $this->img('pc-'.Str::slug($regionName)),
                    'description' => 'Pengurus Cabang menggerakkan kelompok perempuan Maluku di '.$regionName.' melalui kegiatan sosial, pendidikan, dan ekonomi.',
                    'founded_at' => '2012-03-11',
                    'member_count' => $members,
                    'sort_order' => $order,
                    'is_active' => true,
                ],
            );

            $branchRegionIds = Region::query()
                ->where('parent_id', $this->regionId($provinceName))
                ->whereIn('type', ['city', 'regency'])
                ->orderBy('sort_order')
                ->skip($branchIndex * 3)
                ->take(3)
                ->pluck('id');

            foreach ($branchRegionIds as $index => $regionId) {
                Branch::query()->updateOrCreate(
                    ['slug' => 'ranting-'.Str::slug($regionName).'-'.($index + 1)],
                    [
                        'organization_id' => $pc->getKey(),
                        'region_id' => $regionId,
                        'name' => 'Ranting '.($index + 1).' '.$regionName,
                        'type' => 'ranting',
                        'leader_name' => 'Ketua Ranting '.($index + 1).' '.$regionName,
                        'address' => 'Desa binaan '.$regionName,
                        'member_count' => 45 + ($index * 12),
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /**
     * Ambil id wilayah berdasarkan nama.
     */
    private function regionId(string $name): ?int
    {
        return Region::query()->where('slug', Str::slug($name))->value('id');
    }

    /**
     * Susunan pengurus pada tingkat pusat, wilayah, dan cabang.
     */
    private function seedManagements(): void
    {
        $period = '2024 — 2029';

        $pengurus = [
            ['Rosalina Melatunan, S.E.', 'Ketua Umum', 'dpp', null, 'Jakarta', 'Memimpin arah kebijakan organisasi dan hubungan kemitraan nasional.'],
            ['Martha Latuconsina, M.Pd.', 'Wakil Ketua Umum', 'dpp', null, 'Jakarta', 'Mengoordinasikan bidang pendidikan, literasi, dan pengembangan sumber daya anggota.'],
            ['Damaris Siahaya, S.H.', 'Sekretaris Jenderal', 'dpp', null, 'Jakarta', 'Mengelola administrasi, kelembagaan, dan tata kelola organisasi.'],
            ['Yohana Kaya, S.E.', 'Bendahara Umum', 'dpp', null, 'Jakarta', 'Mengelola keuangan organisasi secara transparan dan akuntabel.'],
            ['Jeny Pattikawa, S.K.M.', 'Ketua Bidang Kesehatan', 'dpp', null, 'Jakarta', 'Mengembangkan program kesehatan ibu, anak, dan remaja putri.'],
            ['Irene Sopacua, S.E.', 'Ketua Bidang Ekonomi Kreatif', 'dpp', null, 'Jakarta', 'Mendorong usaha produktif dan penguatan koperasi wanita.'],
            ['Anita Rupilu, S.H., M.H.', 'Ketua Bidang Hukum & HAM', 'dpp', null, 'Jakarta', 'Pendampingan hukum dan advokasi perlindungan perempuan serta anak.'],
            ['Selviana Kaunang, S.Sos.', 'Ketua Bidang Sosial & Budaya', 'dpp', null, 'Jakarta', 'Melestarikan budaya Maluku dan menjalankan kegiatan sosial kemanusiaan.'],
            ['Juliana Tuhuteru, S.Pd.', 'Ketua DPW Maluku', 'dpw', 'Maluku', 'Ambon', 'Memimpin dan mengoordinasikan pengurus cabang di Provinsi Maluku.'],
            ['Sandra Tuasikal, S.E.', 'Sekretaris DPW Maluku', 'dpw', 'Maluku', 'Ambon', 'Mengelola administrasi dan pelaporan kegiatan tingkat provinsi.'],
            ['Fatimah Rahman, S.K.M.', 'Ketua DPW Maluku Utara', 'dpw', 'Maluku Utara', 'Ternate', 'Memimpin dan mengoordinasikan pengurus cabang di Provinsi Maluku Utara.'],
            ['Marlia Usman, S.Pd.', 'Sekretaris DPW Maluku Utara', 'dpw', 'Maluku Utara', 'Ternate', 'Mengelola administrasi dan pelaporan kegiatan tingkat provinsi.'],
            ['Helena Rehatta, S.Sos.', 'Ketua PC Kota Ambon', 'pc', 'Kota Ambon', 'Ambon', 'Menggerakkan anggota di Kota Ambon melalui kegiatan pendidikan dan sosial.'],
            ['Marthina Renyaan, S.Pd.', 'Sekretaris PC Kota Tual', 'pc', 'Kota Tual', 'Tual', 'Mengelola administrasi dan kaderisasi anggota di Kota Tual.'],
            ['Aisyah Umar, S.E.', 'Ketua PC Kota Ternate', 'pc', 'Kota Ternate', 'Ternate', 'Memimpin program literasi dan ekonomi perempuan di Kota Ternate.'],
            ['Hasnah Suleman, S.Ag.', 'Sekretaris PC Kota Tidore Kepulauan', 'pc', 'Kota Tidore Kepulauan', 'Tidore', 'Menjalankan administrasi organisasi dan pelestarian budaya Maluku Utara.'],
        ];

        foreach ($pengurus as $index => [$name, $position, $level, $regionName, $city, $bio]) {
            Management::query()->updateOrCreate(
                ['name' => $name, 'position' => $position],
                [
                    'region_id' => $regionName ? $this->regionId($regionName) : null,
                    'organization_id' => Organization::query()
                        ->where('slug', match ($level) {
                            'dpp' => 'dpp-perwama-ina',
                            'dpw' => 'dpw-perwama-ina-'.Str::slug($regionName),
                            default => 'pc-perwama-ina-'.Str::slug($regionName),
                        })
                        ->value('id'),
                    'level' => $level,
                    'period' => $period,
                    'photo' => $this->img('pengurus-'.Str::slug($name), 600, 600),
                    'email' => Str::slug($name, '.').'@perwama-ina.or.id',
                    'phone' => '0812-'.str_pad((string) (1100 + $index), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (2200 + $index), 4, '0', STR_PAD_LEFT),
                    'bio' => $bio,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * Kategori dan program kerja organisasi.
     */
    private function seedPrograms(): void
    {
        $categories = [
            'pendidikan' => ['Pendidikan & Literasi', 'Beasiswa, literasi digital, dan pendampingan belajar bagi perempuan dan anak Maluku.'],
            'kesehatan' => ['Kesehatan', 'Pemeriksaan kesehatan, edukasi gizi, dan pendampingan kesehatan ibu serta anak.'],
            'ekonomi-kreatif' => ['Ekonomi Kreatif', 'Pelatihan usaha, akses modal, dan penguatan koperasi wanita.'],
            'sosial-budaya' => ['Sosial & Budaya', 'Kegiatan sosial kemanusiaan serta pelestarian budaya Maluku.'],
            'hukum-ham' => ['Hukum & HAM', 'Advokasi perlindungan perempuan dan anak serta literasi hukum.'],
            'lingkungan' => ['Lingkungan', 'Konservasi pesisir, pengelolaan sampah, dan ketahanan pangan.'],
        ];

        $order = 0;

        foreach ($categories as $slug => [$name, $description]) {
            $order++;

            ProgramCategory::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'icon' => 'heroicon-o-academic-cap',
                    'color' => ['#1B3A63', '#2F7A5B', '#B8860B', '#C4622D', '#6B3FA0', '#0E7490'][$order - 1],
                    'sort_order' => $order,
                    'is_active' => true,
                ],
            );
        }

        $this->seedProgramList();
    }

    /**
     * Daftar program unggulan beserta capaiannya.
     */
    private function seedProgramList(): void
    {
        $programs = $this->programData();

        foreach ($programs as $index => $p) {
            Program::query()->updateOrCreate(
                ['slug' => Str::slug($p['t'])],
                [
                    'program_category_id' => ProgramCategory::query()->where('slug', $p['cat'])->value('id'),
                    'region_id' => $this->regionId($p['reg']),
                    'title' => $p['t'],
                    'type' => 'kerja_anggaran',
                    'excerpt' => $p['excerpt'],
                    'description' => $p['desc'],
                    'goal' => $p['goal'],
                    'objectives' => $p['obj'],
                    'target_participants' => $p['target'],
                    'achievements' => $p['ach'],
                    'cover_image' => $this->img('program-'.$p['cat'].'-'.$index),
                    'budget' => $p['budget'],
                    'fiscal_year' => '2026',
                    'status' => $p['status'],
                    'is_featured' => $p['feat'],
                    'views' => 120 + ($index * 17),
                    'sort_order' => $index + 1,
                    'meta_title' => $p['t'].' — PERWAMA INA',
                    'meta_description' => $p['excerpt'],
                    'published_at' => now()->subDays(20 - $index),
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * Data program unggulan.
     *
     * @return array<int, array<string, mixed>>
     */
    private function programData(): array
    {
        return [
            [
                't' => 'Beasiswa Perempuan Maluku Berprestasi',
                'cat' => 'pendidikan', 'reg' => 'Maluku', 'status' => 'ongoing', 'feat' => true, 'budget' => 450000000,
                'excerpt' => 'Beasiswa pendidikan tinggi bagi perempuan Maluku berprestasi dari keluarga prasejahtera.',
                'desc' => '<p>Program beasiswa ini mendampingi perempuan Maluku berprestasi agar dapat menempuh pendidikan tinggi tanpa terbebani biaya. Selain bantuan biaya kuliah, penerima beasiswa mengikuti pendampingan akademik, mentoring karier, dan kegiatan pengabdian di kampung halaman.</p><p>Program dikelola bersama Dewan Pengurus Wilayah Maluku dan Maluku Utara dengan verifikasi data yang transparan.</p>',
                'goal' => 'Membuka akses pendidikan tinggi bagi 150 perempuan Maluku dari keluarga prasejahtera setiap tahun.',
                'obj' => "Penerima beasiswa tingkat sarjana di 12 perguruan tinggi mitra\nPendampingan akademik dan mentoring karier bagi seluruh penerima\nKegiatan pengabdian masyarakat di desa asal penerima",
                'target' => 'Perempuan Maluku usia 17—25 tahun dengan prestasi akademik dan kondisi ekonomi prasejahtera.',
                'ach' => "312 penerima beasiswa sejak 2019\n86 persen penerima lulus tepat waktu\n45 penerima melanjutkan studi pascasarjana",
            ],
            [
                't' => 'Sekolah Literasi Digital Perempuan Desa',
                'cat' => 'pendidikan', 'reg' => 'Maluku Utara', 'status' => 'ongoing', 'feat' => true, 'budget' => 275000000,
                'excerpt' => 'Pelatihan literasi digital dan keamanan berinternet bagi perempuan di desa pesisir.',
                'desc' => '<p>Kelas literasi digital digelar di balai desa dan pusat kegiatan belajar masyarakat. Materi mencakup penggunaan perangkat pintar, pemasaran produk melalui media sosial, serta perlindungan data pribadi dan pencegahan kekerasan daring.</p>',
                'goal' => 'Meningkatkan keterampilan digital 2.000 perempuan desa agar mampu memanfaatkan teknologi secara produktif dan aman.',
                'obj' => "Pelatihan dasar perangkat pintar dan aplikasi produktif\nModul keamanan digital dan pencegahan kekerasan daring\nPendampingan pemasaran produk UMKM secara daring",
                'target' => 'Perempuan desa pesisir dan kepulauan usia 18—55 tahun.',
                'ach' => "1.840 peserta terlatih di 46 desa\n320 UMKM binaan aktif berjualan daring\n18 fasilitator desa bersertifikat",
            ],
            [
                't' => 'Pos Sehat Ibu dan Anak',
                'cat' => 'kesehatan', 'reg' => 'Maluku', 'status' => 'ongoing', 'feat' => true, 'budget' => 320000000,
                'excerpt' => 'Layanan pemeriksaan kesehatan ibu, anak, dan remaja putri di wilayah kepulauan.',
                'desc' => '<p>Pos Sehat menyediakan pemeriksaan kehamilan, imunisasi, pemberian makanan tambahan, serta edukasi gizi bagi keluarga. Layanan ini dijalankan bersama kader posyandu dan puskesmas setempat.</p><p>Pemeriksaan menumpang kapal kesehatan untuk menjangkau pulau-pulau kecil.</p>',
                'goal' => 'Menurunkan angka anemia dan kekurangan gizi pada ibu hamil serta anak balita di wilayah kerja.',
                'obj' => "Pemeriksaan kehamilan berkala bagi ibu hamil\nEdukasi gizi keluarga dan pemberian makanan tambahan\nSkrining anemia bagi remaja putri sekolah",
                'target' => 'Ibu hamil, balita, dan remaja putri di 30 desa kepulauan.',
                'ach' => "3.100 ibu dan anak terlayani\n62 persen penurunan kasus anemia remaja putri\nKapal sehat melayani 12 pulau kecil",
            ],
            [
                't' => 'Koperasi Wanita Maluku Mandiri',
                'cat' => 'ekonomi-kreatif', 'reg' => 'Maluku', 'status' => 'ongoing', 'feat' => false, 'budget' => 500000000,
                'excerpt' => 'Penguatan koperasi wanita, akses modal usaha, dan pendampingan pemasaran produk.',
                'desc' => '<p>Program ini mendampingi kelompok usaha perempuan dalam membentuk koperasi, menyusun laporan keuangan sederhana, dan memasarkan produk seperti tenun, ikan asap, serta olahan sagu.</p>',
                'goal' => 'Menguatkan 40 koperasi dan kelompok usaha perempuan agar memiliki pendapatan yang stabil.',
                'obj' => "Pembentukan dan penguatan badan usaha koperasi wanita\nPelatihan manajemen keuangan dan pencatatan usaha\nFasilitasi akses pembiayaan serta pemasaran produk",
                'target' => 'Kelompok usaha perempuan dan koperasi wanita binaan di Maluku serta Maluku Utara.',
                'ach' => "38 koperasi wanita aktif\n1.250 anggota memiliki usaha produktif\nPeningkatan pendapatan rata-rata 34 persen",
            ],
            [
                't' => 'Sekolah Adat dan Pelestarian Bahasa Daerah',
                'cat' => 'sosial-budaya', 'reg' => 'Maluku', 'status' => 'ongoing', 'feat' => false, 'budget' => 185000000,
                'excerpt' => 'Pengenalan bahasa, tarian, dan tradisi Maluku bagi generasi muda perantau.',
                'desc' => '<p>Sekolah Adat digelar setiap akhir pekan di sanggar budaya binaan. Anak-anak Maluku di perantauan belajar bahasa daerah, tarian, lagu, serta nilai adat seperti pela dan gandong.</p>',
                'goal' => 'Merawat identitas budaya Maluku pada generasi muda di 20 kota perantauan.',
                'obj' => "Kelas bahasa daerah untuk anak usia 7—15 tahun\nLatihan tarian dan musik tradisional Maluku\nFestival budaya tahunan bersama komunitas",
                'target' => 'Anak dan remaja keluarga Maluku di kota perantauan.',
                'ach' => "1.260 anak mengikuti kelas budaya\n24 sanggar budaya aktif\nFestival Maluku tahunan dihadiri 3.000 orang",
            ],
            [
                't' => 'Pendampingan Hukum Perempuan dan Anak',
                'cat' => 'hukum-ham', 'reg' => 'Maluku Utara', 'status' => 'ongoing', 'feat' => true, 'budget' => 240000000,
                'excerpt' => 'Layanan konsultasi hukum serta pendampingan korban kekerasan berbasis gender.',
                'desc' => '<p>Tim paralegal PERWAMA INA menerima laporan, mendampingi korban saat proses pelaporan, dan mengoordinasikan rujukan ke layanan kesehatan serta rumah aman.</p><p>Edukasi hukum juga diberikan kepada tokoh agama, adat, dan guru agar korban berani melapor.</p>',
                'goal' => 'Memastikan setiap perempuan dan anak korban kekerasan memperoleh perlindungan serta keadilan.',
                'obj' => "Klinik konsultasi hukum mingguan\nPelatihan paralegal tingkat cabang\nSosialisasi pencegahan kekerasan berbasis gender di sekolah",
                'target' => 'Perempuan dan anak korban kekerasan serta komunitas pendamping.',
                'ach' => "486 kasus didampingi\n64 paralegal terlatih\n12 rumah aman mitra di Maluku dan Maluku Utara",
            ],
            [
                't' => 'Perempuan Menjaga Pesisir',
                'cat' => 'lingkungan', 'reg' => 'Maluku', 'status' => 'planned', 'feat' => false, 'budget' => 210000000,
                'excerpt' => 'Gerakan penanaman mangrove dan pengelolaan sampah pesisir oleh kelompok perempuan.',
                'desc' => '<p>Kelompok perempuan desa menjadi penggerak penanaman mangrove, pembersihan pantai, dan pengolahan sampah organik menjadi kompos untuk kebun rumah.</p>',
                'goal' => 'Memulihkan ekosistem pesisir sekaligus membuka sumber penghidupan baru bagi keluarga.',
                'obj' => "Penanaman 50.000 bibit mangrove di 15 desa pesisir\nPelatihan pengolahan sampah organik rumah tangga\nPendirian bank sampah berbasis kelompok perempuan",
                'target' => 'Kelompok perempuan di desa pesisir Maluku dan Maluku Utara.',
                'ach' => "Pilot project di 4 desa berjalan sejak 2025\n320 perempuan terlibat sebagai kader lingkungan",
            ],
            [
                't' => 'Dapur Peduli Bencana',
                'cat' => 'sosial-budaya', 'reg' => 'Maluku Utara', 'status' => 'ongoing', 'feat' => false, 'budget' => 160000000,
                'excerpt' => 'Dapur umum dan logistik cepat tanggap bencana bersama relawan perempuan.',
                'desc' => '<p>Saat terjadi banjir, gempa, atau cuaca ekstrem, tim relawan perempuan PERWAMA INA menyiapkan dapur umum dan menyalurkan paket kebutuhan dasar bagi keluarga terdampak.</p>',
                'goal' => 'Memberikan respons bantuan paling lambat 24 jam setelah bencana terjadi.',
                'obj' => "Pembentukan tim relawan tanggap bencana di tiap cabang\nPenyediaan logistik dan peralatan dapur umum\nPsikososial dasar bagi perempuan dan anak terdampak",
                'target' => 'Keluarga terdampak bencana di wilayah Maluku dan Maluku Utara.',
                'ach' => "18 kali respons bencana\n7.400 paket bantuan tersalurkan\n120 relawan perempuan terlatih",
            ],
        ];
    }

    /**
     * Agenda dan kegiatan yang telah dilaksanakan.
     */
    private function seedEventsAndActivities(): void
    {
        $events = [
            ['Pelatihan Kewirausahaan Perempuan Pesisir', 'upcoming', 'Maluku', 'Kota Ambon', 'Aula Dinas Perikanan Kota Ambon', 'Ny. Rosalina Melatunan, S.E.', 21, 'Pelatihan tiga hari untuk penyusunan rencana usaha, pencatatan keuangan, dan pemasaran digital bagi kelompok perempuan pesisir.'],
            ['Seminar Nasional Perempuan Maluku Berdaya', 'upcoming', 'DKI Jakarta', 'DKI Jakarta', 'Balai Kartini, Jakarta Selatan', 'Prof. Dr. Maria Ruhulessin', 45, 'Seminar tahunan yang menghadirkan akademisi, pelaku usaha, dan penggerak komunitas untuk membahas agenda pemberdayaan perempuan Maluku.'],
            ['Festival Budaya Maluku 2026', 'upcoming', 'Maluku Utara', 'Kota Ternate', 'Lapangan Nukila, Ternate', 'Sanggar Budaya Ternate', 60, 'Pesta budaya menampilkan tarian, musik, dan kuliner khas Maluku bersama komunitas dari berbagai kota.'],
            ['Klinik Hukum Gratis untuk Perempuan', 'ongoing', 'Maluku', 'Kota Tual', 'Sekretariat PC Kota Tual', 'Tim Paralegal PERWAMA INA', 14, 'Layanan konsultasi hukum gratis bagi perempuan dan anak, termasuk pendampingan kasus kekerasan berbasis gender.'],
            ['Rapat Kerja Nasional 2025', 'completed', 'DKI Jakarta', 'DKI Jakarta', 'Hotel Bidakara, Jakarta Selatan', 'Dewan Pengurus Pusat', -180, 'Rapat kerja nasional yang menetapkan arah program organisasi serta evaluasi capaian tahun sebelumnya.'],
            ['Bakti Sosial Pasca Cuaca Ekstrem', 'completed', 'Maluku', 'Kabupaten Maluku Tengah', 'Desa Waai, Maluku Tengah', 'Tim Relawan PERWAMA INA', -240, 'Penyaluran bantuan kebutuhan dasar serta dapur umum bagi keluarga terdampak cuaca ekstrem di pesisir Seram.'],
        ];

        $order = 0;

        foreach ($events as [$title, $status, $region, $city, $location, $speaker, $dayOffset, $description]) {
            $order++;
            $date = now()->addDays($dayOffset);

            Event::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'region_id' => $this->regionId($region),
                    'organization_id' => Organization::query()
                        ->where('slug', 'pc-perwama-ina-'.Str::slug($city))
                        ->orWhere('slug', 'dpw-perwama-ina-'.Str::slug($region))
                        ->value('id'),
                    'program_id' => Program::query()->inRandomOrder()->value('id'),
                    'title' => $title,
                    'description' => $description."\n\nInformasi lengkap dan pendaftaran dapat menghubungi sekretariat PERWAMA INA melalui WhatsApp 0812-9000-1122.",
                    'start_date' => $date->toDateString(),
                    'start_time' => '08.30',
                    'end_date' => $date->copy()->addDays($status === 'completed' ? 0 : 1)->toDateString(),
                    'end_time' => '16.00',
                    'location' => $location,
                    'poster' => $this->img('agenda-'.$order),
                    'speaker' => $speaker,
                    'status' => $status,
                    'is_featured' => $order <= 2,
                    'views' => 80 + $order * 12,
                    'published_at' => now()->subDays(30 - $order),
                ],
            );
        }

        $this->seedActivities();
    }

    /**
     * Kegiatan yang sudah dipublikasikan.
     */
    private function seedActivities(): void
    {
        $activities = [
            ['Pelatihan Menjahit untuk Perempuan Kepulauan', 'pelatihan', 'Maluku', 'Kota Tual', -35, 48, 'Peserta mempelajari dasar menjahit, pengukuran pola, dan penyelesaian produk siap jual untuk pasar lokal.', 'Balai Desa, Kota Tual'],
            ['Pemeriksaan Kesehatan Gratis di Pulau Seram', 'kegiatan_sosial', 'Maluku', 'Kabupaten Maluku Tengah', -60, 320, 'Layanan pemeriksaan umum, pemeriksaan kehamilan, dan pemberian vitamin bagi warga desa pesisir.', 'Desa Waai, Maluku Tengah'],
            ['Seminar Keamanan Digital bagi Remaja Putri', 'seminar', 'Maluku Utara', 'Kota Ternate', -75, 180, 'Sosialisasi perlindungan data pribadi, etika bermedia sosial, dan pencegahan kekerasan daring.', 'Aula Kampus, Ternate'],
            ['Rapat Koordinasi Pengurus Cabang', 'organisasi', 'Maluku', 'Kota Ambon', -90, 64, 'Koordinasi program semester, evaluasi capaian, dan penyusunan rencana kegiatan wilayah.', 'Sekretariat DPW Maluku, Ambon'],
            ['Pelatihan Olahan Hasil Laut', 'pelatihan', 'Maluku', 'Kota Ambon', -105, 96, 'Pelatihan pengolahan ikan asap, abon, dan kerupuk untuk meningkatkan nilai jual hasil tangkapan keluarga.', 'Sentra UMKM Ambon'],
            ['Penanaman Mangrove Bersama Kader Lingkungan', 'kegiatan_sosial', 'Maluku', 'Kabupaten Buru', -130, 150, 'Aksi penanaman 5.000 bibit mangrove serta edukasi pengelolaan sampah pesisir bersama warga.', 'Pesisir Namlea, Buru'],
            ['Klinik Hukum dan Pendampingan Korban', 'lainnya', 'Maluku Utara', 'Kota Tidore Kepulauan', -150, 42, 'Konsultasi hukum gratis dan pendampingan korban kekerasan berbasis gender bersama aparat setempat.', 'Sekretariat PC Tidore'],
            ['Bakti Sosial Ramadan Keluarga Maluku', 'kegiatan_sosial', 'DKI Jakarta', 'DKI Jakarta', -170, 240, 'Penyaluran paket sembako dan santunan bagi keluarga Maluku prasejahtera di Jabodetabek.', 'Cipete Selatan, Jakarta'],
            ['Sekolah Adat: Kelas Tarian dan Bahasa Maluku', 'organisasi', 'Jawa Barat', 'Jawa Barat', -195, 120, 'Kelas budaya akhir pekan untuk anak-anak keluarga Maluku di perantauan.', 'Sanggar Budaya, Bandung'],
        ];

        foreach ($activities as $index => [$title, $category, $region, $city, $dayOffset, $participants, $excerpt, $location]) {
            Activity::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'region_id' => $this->regionId($city ?: $region),
                    'organization_id' => Organization::query()
                        ->where('slug', 'pc-perwama-ina-'.Str::slug($city))
                        ->orWhere('slug', 'dpw-perwama-ina-'.Str::slug($region))
                        ->value('id'),
                    'program_id' => Program::query()->inRandomOrder()->value('id'),
                    'title' => $title,
                    'category' => $category,
                    'excerpt' => $excerpt,
                    'description' => '<p>'.$excerpt.' Kegiatan berlangsung dengan pendampingan fasilitator daerah dan melibatkan kelompok perempuan binaan PERWAMA INA.</p><p>Seluruh rangkaian kegiatan didokumentasikan sebagai bahan evaluasi serta pembelajaran bagi pengurus cabang lain.</p>',
                    'activity_date' => now()->addDays($dayOffset)->toDateString(),
                    'location' => $location,
                    'speaker' => 'Tim Fasilitator PERWAMA INA',
                    'participant_count' => $participants,
                    'cover_image' => $this->img('kegiatan-'.$index),
                    'is_featured' => $index < 3,
                    'views' => 90 + ($index * 15),
                    'meta_title' => $title.' — PERWAMA INA',
                    'meta_description' => $excerpt,
                    'published_at' => now()->addDays($dayOffset + 2),
                ],
            );
        }
    }

    /**
     * Album galeri beserta isinya (foto dan video).
     */
    private function seedGalleries(): void
    {
        $albums = [
            ['HUT PERWAMA INA ke-28', 'hut_perwama_ina', 'DKI Jakarta', 'Perayaan ulang tahun organisasi bersama pengurus, anggota, dan mitra dari seluruh Indonesia.', true, -95],
            ['Pelatihan Menjahit Perempuan Tual', 'pelatihan', 'Kota Tual', 'Dokumentasi pelatihan keterampilan menjahit bagi perempuan kepulauan di Kota Tual.', true, -35],
            ['Bakti Sosial Pesisir Seram', 'bakti_sosial', 'Kabupaten Maluku Tengah', 'Penyaluran bantuan dan layanan kesehatan bagi keluarga pesisir Pulau Seram.', false, -60],
            ['Seminar Nasional Perempuan Maluku', 'seminar', 'DKI Jakarta', 'Kegiatan seminar tahunan dengan pembicara akademisi dan penggerak komunitas.', false, -45],
            ['Rapat Kerja Nasional 2025', 'rapat_organisasi', 'DKI Jakarta', 'Rapat kerja nasional yang menetapkan arah program organisasi.', false, -180],
            ['Festival Budaya Maluku di Ternate', 'budaya', 'Kota Ternate', 'Pesta budaya menampilkan tarian, musik, dan kuliner khas Maluku.', true, -120],
        ];

        $videoUrls = [
            'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
            'https://www.youtube.com/watch?v=ScMzIvxBSi4',
            'https://www.youtube.com/watch?v=e-ORhEE9VVg',
        ];

        $albumCategoryIds = AlbumCategory::query()->pluck('id', 'slug');

        foreach ($albums as $index => [$title, $album, $region, $description, $featured, $dayOffset]) {
            $gallery = Gallery::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'region_id' => $this->regionId($region),
                    'program_id' => Program::query()->inRandomOrder()->value('id'),
                    'title' => $title,
                    'description' => $description,
                    'album_category_id' => $albumCategoryIds[$album] ?? null,
                    'cover_image' => $this->img('album-'.$index),
                    'event_date' => now()->addDays($dayOffset)->toDateString(),
                    'is_featured' => $featured,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );

            for ($item = 1; $item <= 5; $item++) {
                $isVideo = $item > 3;

                GalleryItem::query()->updateOrCreate(
                    [
                        'gallery_id' => $gallery->getKey(),
                        'sort_order' => $item,
                    ],
                    [
                        'type' => $isVideo ? 'video' : 'photo',
                        'file' => $isVideo ? null : $this->img('album-'.$index.'-'.$item, 1000, 750),
                        'video_url' => $isVideo ? $videoUrls[($index + $item) % count($videoUrls)] : null,
                        'thumbnail' => $isVideo ? $this->img('album-'.$index.'-'.$item, 800, 600) : null,
                        'caption' => $title.' — dokumentasi '.$item,
                    ],
                );
            }

            $activityIds = Activity::query()
                ->whereNull('gallery_id')
                ->where('region_id', $gallery->region_id)
                ->take(2)
                ->pluck('id');

            if ($activityIds->isEmpty()) {
                $activityIds = Activity::query()->whereNull('gallery_id')->take(1)->pluck('id');
            }

            if ($activityIds->isNotEmpty()) {
                Activity::query()->whereIn('id', $activityIds)->update(['gallery_id' => $gallery->getKey()]);
            }
        }
    }

    /**
     * Video profil, dokumentasi, dan sosialisasi.
     */
    private function seedVideos(): void
    {
        $videos = [
            ['Profil PERWAMA INA — Perempuan Maluku untuk Indonesia', 'profil', 'aqz-KE-bpKQ', true, -210, 'Mengenal lebih dekat sejarah, visi, dan gerakan PERWAMA INA dalam memberdayakan perempuan Maluku di seluruh Indonesia. Video ini merangkum perjalanan organisasi sejak 1998 hingga jejaring pengurus cabang di 18 provinsi.'],
            ['Dokumentasi HUT PERWAMA INA ke-28', 'kegiatan', 'ScMzIvxBSi4', true, -95, 'Rangkaian perayaan ulang tahun ke-28 organisasi bersama pengurus, anggota, dan mitra dari berbagai daerah. Acara diisi dialog perempuan Maluku, pameran produk usaha anggota, serta penyerahan beasiswa pendidikan.'],
            ['Program Beasiswa Perempuan Maluku Berprestasi', 'program', 'e-ORhEE9VVg', false, -80, 'Cerita para penerima beasiswa yang kini menempuh pendidikan tinggi dan kembali mengabdi di kampung halaman. Program ini mendampingi 150 penerima setiap tahun melalui bantuan biaya kuliah dan pendampingan akademik.'],
            ['Pelatihan Literasi Digital Perempuan Desa', 'pelatihan', 'aqz-KE-bpKQ', false, -65, 'Kegiatan pelatihan literasi digital dan keamanan berinternet bagi perempuan desa pesisir. Pelatihan digelar di balai desa bersama 18 fasilitator bersertifikat dan menjangkau 46 desa di Maluku.'],
            ['Sosialisasi Perlindungan Perempuan dan Anak', 'sosialisasi', 'ScMzIvxBSi4', false, -50, 'Sosialisasi pencegahan kekerasan berbasis gender bersama tokoh agama, adat, dan guru. Materi mencakup jalur pelaporan, pendampingan paralegal, serta pemulihan psikososial bagi korban.'],
            ['Festival Budaya Maluku 2025', 'kegiatan', 'e-ORhEE9VVg', false, -120, 'Pesta budaya menampilkan tarian, musik, dan kuliner khas Maluku bersama komunitas perantau. Festival ini juga menjadi ruang perkenalan bahasa daerah bagi anak-anak keluarga Maluku di perantauan.'],
        ];

        foreach ($videos as $index => [$title, $category, $youtubeId, $featured, $dayOffset, $description]) {
            Video::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'description' => $description,
                    'url' => 'https://www.youtube.com/watch?v='.$youtubeId,
                    'thumbnail' => $this->img('video-'.$index, 800, 450),
                    'category' => $category,
                    'is_featured' => $featured,
                    'sort_order' => $index + 1,
                    'published_at' => now()->addDays($dayOffset),
                ],
            );
        }
    }

    /**
     * Berita, artikel, dan pengumuman.
     */
    private function seedNewsAndStories(): void
    {
        $this->seedNews();
        $this->seedStories();
    }

    private function seedNews(): void
    {
        $news = [
            ['PERWAMA INA Buka Pendaftaran Beasiswa Perempuan Maluku 2026', 'pengumuman', 'pendidikan', 'Maluku', 'Sekretariat Nasional', -3, 'Pendaftaran beasiswa pendidikan tinggi bagi perempuan Maluku berprestasi dibuka hingga akhir bulan ini.', 'Program beasiswa ini menyediakan bantuan biaya kuliah, biaya hidup, serta pendampingan akademik bagi 150 penerima setiap tahun.'],
            ['Pelatihan Literasi Digital Menjangkau 46 Desa di Maluku', 'berita', 'kegiatan', 'Maluku Utara', 'Redaksi PERWAMA INA', -6, 'Sebanyak 1.840 perempuan desa mengikuti pelatihan literasi digital dan keamanan berinternet sepanjang tahun ini.', 'Pelatihan digelar di balai desa dan pusat kegiatan belajar masyarakat dengan pendampingan 18 fasilitator desa bersertifikat.'],
            ['Koperasi Wanita Binaan Catat Kenaikan Pendapatan 34 Persen', 'berita', 'ekonomi', 'Maluku', 'Redaksi PERWAMA INA', -10, 'Pendampingan manajemen keuangan dan pemasaran digital membuat pendapatan anggota koperasi binaan meningkat.', 'Program Koperasi Wanita Maluku Mandiri mendampingi 38 koperasi dengan total 1.250 anggota usaha produktif.'],
            ['Opini: Perempuan Maluku dan Tantangan Ekonomi Biru', 'opini', 'perempuan', 'Maluku', 'Dr. Johana Talahatu', -14, 'Ekonomi biru membuka ruang besar bagi perempuan pesisir, asalkan akses modal dan pelatihan keterampilan diperluas.', 'Perempuan pesisir memiliki pengetahuan lokal tentang hasil laut yang dapat menjadi fondasi usaha bernilai tambah.'],
            ['DPW Maluku Utara Gelar Rapat Kerja Wilayah', 'berita', 'organisasi', 'Maluku Utara', 'Redaksi PERWAMA INA', -18, 'Rapat kerja wilayah menetapkan prioritas program pendidikan, kesehatan, dan advokasi perlindungan perempuan.', 'Rapat dihadiri pengurus cabang dari seluruh kabupaten dan kota di Maluku Utara.'],
            ['PERWAMA INA Dampingi 486 Kasus Kekerasan Perempuan dan Anak', 'berita', 'hukum_ham', 'Maluku', 'Redaksi PERWAMA INA', -22, 'Tim paralegal organisasi mendampingi korban mulai dari pelaporan hingga pemulihan psikososial.', 'Pendampingan dilakukan bersama layanan kesehatan, rumah aman mitra, serta aparat penegak hukum setempat.'],
            ['Artikel: Merawat Bahasa Maluku di Rantau', 'artikel', 'budaya', 'DKI Jakarta', 'Maria Soplanit, M.Pd.', -26, 'Sekolah adat binaan PERWAMA INA menghadirkan kembali bahasa dan tradisi Maluku bagi anak-anak perantau.', 'Kelas budaya digelar akhir pekan di 24 sanggar budaya yang tersebar di berbagai kota.'],
            ['Relawan Perempuan Bergerak Tanggap Bencana di Pesisir Seram', 'berita', 'sosial', 'Maluku', 'Redaksi PERWAMA INA', -30, 'Dapur umum dan penyaluran paket bantuan kebutuhan dasar dilakukan dalam 24 jam setelah cuaca ekstrem melanda.', 'Sebanyak 120 relawan perempuan terlatih terlibat dalam respons bencana sepanjang tahun.'],
        ];

        foreach ($news as $index => [$title, $type, $category, $region, $author, $dayOffset, $excerpt, $lead]) {
            News::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'region_id' => $this->regionId($region),
                    'program_id' => Program::query()->inRandomOrder()->value('id'),
                    'title' => $title,
                    'type' => $type,
                    'category' => $category,
                    'excerpt' => $excerpt,
                    'content' => '<p>'.$excerpt.' '.$lead.'</p><p>Kegiatan ini merupakan bagian dari komitmen PERWAMA INA untuk memperkuat peran perempuan Maluku di bidang pendidikan, kesehatan, ekonomi, sosial, hukum, dan budaya.</p><p>Sekretariat nasional membuka ruang kolaborasi bagi individu maupun lembaga yang ingin mendukung program-program tersebut melalui kanal resmi organisasi.</p>',
                    'cover_image' => $this->img('berita-'.$index),
                    'author' => $author,
                    'source' => 'PERWAMA INA',
                    'is_featured' => $index < 3,
                    'views' => 150 + ($index * 23),
                    'meta_title' => $title,
                    'meta_description' => $excerpt,
                    'published_at' => now()->addDays($dayOffset),
                ],
            );
        }
    }

    /**
     * Kisah inspiratif dan tokoh perempuan Maluku.
     */
    private function seedStories(): void
    {
        $stories = [
            ['Dari Pulau Kecil ke Bangku Kuliah', 'kisah_inspiratif', 'Maluku', 'Natalia Kaimudin', 21, 'Penerima Beasiswa Perempuan Maluku Berprestasi', -12, 'Anak nelayan dari Pulau Saparua kini menempuh pendidikan kedokteran berkat beasiswa dan pendampingan PERWAMA INA.', 'Pendidikan adalah jalan keluar bagi keluarga saya, dan PERWAMA INA membuka pintu itu.', "Tumbuh di keluarga nelayan dengan keterbatasan ekonomi, Natalia harus berjalan dua jam setiap hari ke sekolah menengah.\nSetelah lolos seleksi beasiswa tahun 2022, ia pindah ke Ambon untuk menempuh pendidikan kedokteran.", 'Aktif dalam pendampingan akademik dan kegiatan pengabdian kesehatan di desa asal setiap libur semester.', 'Menjadi motivasi bagi 40 siswa SMA di kampungnya untuk melanjutkan pendidikan tinggi.'],
            ['Perempuan Penenun yang Menembus Pasar Nasional', 'tokoh_perempuan', 'Kabupaten Maluku Tenggara', 'Yuliana Renhoran', 45, 'Perajin tenun dan Ketua Koperasi Wanita', -20, 'Yuliana mengubah tenun tradisional Maluku Tenggara menjadi produk bernilai jual tinggi dan memberdayakan 60 penenun.', 'Motif tenun kami adalah cerita keluarga. Kami hanya perlu ruang agar cerita itu sampai ke luar pulau.', "Mewarisi keterampilan menenun dari ibunya, Yuliana memulai usaha dengan dua alat tenun pada 2014.\nBergabung dengan program Koperasi Wanita Maluku Mandiri membuat usahanya tumbuh dan diikuti puluhan penenun lain.", 'Memimpin pelatihan motif baru, pengelolaan keuangan koperasi, serta pemasaran daring produk tenun.', 'Pendapatan 60 penenun meningkat dua kali lipat dan produknya kini dipasarkan ke Jakarta serta Surabaya.'],
            ['Guru Inspiratif dari Desa Pesisir Buru', 'perempuan_maluku', 'Kabupaten Buru', 'Sarah Wael', 38, 'Guru dan Fasilitator Literasi Digital', -28, 'Sarah mendirikan kelas literasi digital di desanya setelah mengikuti pelatihan PERWAMA INA.', 'Keterampilan digital membuat perempuan desa tidak lagi sekadar penonton perubahan.', "Mengajar di sekolah dasar pesisir Buru, Sarah melihat banyak ibu muda kesulitan memasarkan hasil kebun dan tangkapan ikan.\nSetelah mengikuti pelatihan literasi digital, ia membuka kelas mingguan di balai desa.", 'Mendampingi 240 ibu muda memanfaatkan media sosial untuk memasarkan produk rumahan.', '78 usaha rumah tangga baru lahir dan omzet penjualan desa meningkat signifikan.'],
            ['Bidan Desa yang Melayani Tiga Pulau', 'kisah_inspiratif', 'Kabupaten Maluku Tenggara', 'Marlen Tuhuteru', 34, 'Bidan Desa dan Relawan Pos Sehat', -35, 'Marlen mendayung perahu antar pulau untuk memastikan ibu hamil tetap terlayani meski keterbatasan akses.', 'Setiap ibu hamil di pulau ini berhak mendapatkan layanan terbaik, sejauh apa pun jaraknya.', "Bertugas sebagai bidan desa sejak 2016, Marlen rutin melakukan pemeriksaan kehamilan di tiga pulau berdekatan.\nProgram Pos Sehat Ibu dan Anak membantunya menyediakan vitamin serta makanan tambahan.", 'Melakukan pemeriksaan kehamilan, imunisasi, serta edukasi gizi bagi keluarga di tiga pulau.', 'Risiko anemia pada remaja putri setempat menurun dan seluruh persalinan tahun lalu ditangani tenaga kesehatan.'],
            ['Perempuan Penjaga Mangrove Pesisir Namlea', 'cerita_komunitas', 'Kabupaten Buru', 'Komunitas Kader Lingkungan Namlea', 40, 'Kelompok Perempuan Penggerak Lingkungan', -45, 'Kelompok ibu-ibu di Namlea menanam ribuan bibit mangrove untuk melindungi perkampungan dari abrasi.', 'Kami menanam untuk anak cucu, agar kampung ini tetap ada saat mereka besar.', "Kelompok ini terbentuk setelah program Perempuan Menjaga Pesisir memperkenalkan penanaman mangrove pada 2025.\nSejak itu, mereka rutin menanam serta mengolah sampah organik menjadi kompos.", 'Penanaman 8.000 bibit mangrove, pembersihan pantai bulanan, dan pengelolaan bank sampah.', 'Abrasi di dua dusun berkurang dan 30 keluarga mendapat tambahan penghasilan dari produk kompos.'],
            ['Advokat Muda bagi Korban Kekerasan', 'tokoh_perempuan', 'Maluku Utara', 'Fitriani Abdulah', 31, 'Paralegal PERWAMA INA', -55, 'Fitriani mendampingi korban kekerasan berbasis gender agar berani melapor dan mendapatkan keadilan.', 'Pendampingan membuat korban tahu bahwa ia tidak berjalan sendiri.', "Berlatar pendidikan hukum, Fitriani memilih menjadi paralegal setelah melihat banyak korban kekerasan tidak mengetahui haknya.\nIa bergabung dengan tim pendampingan hukum PERWAMA INA sejak 2021.", 'Mendampingi pelaporan, berkoordinasi dengan rumah aman mitra, serta memberikan sosialisasi di sekolah dan gereja.', 'Sebanyak 486 kasus didampingi dan kesadaran masyarakat untuk melaporkan kekerasan meningkat.'],
        ];

        foreach ($stories as $index => [$title, $category, $region, $person, $age, $role, $dayOffset, $excerpt, $quote, $journey, $activities, $impact]) {
            Story::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'region_id' => $this->regionId($region),
                    'program_id' => Program::query()->inRandomOrder()->value('id'),
                    'title' => $title,
                    'category' => $category,
                    'excerpt' => $excerpt,
                    'content' => '<p>'.$excerpt.'</p><p>'.$role.' ini merupakan salah satu penerima manfaat program PERWAMA INA yang dijalankan bersama pengurus wilayah serta cabang setempat.</p>',
                    'person_name' => $person,
                    'person_photo' => $this->img('tokoh-'.$index, 600, 600),
                    'person_age' => $age,
                    'cover_image' => $this->img('kisah-'.$index),
                    'quote' => $quote,
                    'journey' => $journey,
                    'activities' => $activities,
                    'impact' => $impact,
                    'is_featured' => $index < 3,
                    'views' => 130 + ($index * 19),
                    'meta_title' => $title,
                    'meta_description' => $excerpt,
                    'published_at' => now()->addDays($dayOffset),
                ],
            );
        }
    }

    /**
     * Publikasi digital: laporan, modul, buku, dan kajian.
     */
    private function seedPublications(): void
    {
        $file = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

        $publications = [
            ['Laporan Tahunan PERWAMA INA 2025', 'laporan', '2025', 84, 5450000, 640, true, 'Rekapitulasi program, capaian indikator, serta laporan keuangan organisasi sepanjang tahun 2025.'],
            ['Laporan Tahunan PERWAMA INA 2024', 'laporan', '2024', 78, 5130000, 512, false, 'Laporan pertanggungjawaban pengurus pusat beserta capaian program di seluruh wilayah.'],
            ['Modul Literasi Digital untuk Perempuan Desa', 'modul', '2025', 48, 3250000, 388, true, 'Bahan ajar pelatihan literasi digital, keamanan berinternet, dan pemasaran produk daring.'],
            ['Panduan Pendirian dan Pengelolaan Koperasi Wanita', 'panduan', '2024', 36, 2450000, 274, false, 'Langkah praktis membentuk koperasi wanita, menyusun administrasi, hingga pelaporan keuangan sederhana.'],
            ['Buku Cerita Perempuan Maluku Berdaya', 'buku', '2024', 120, 7960000, 431, true, 'Kumpulan kisah perempuan Maluku yang menggerakkan perubahan di kampung halaman dan perantauan.'],
            ['Kajian Kekerasan Berbasis Gender di Maluku', 'kajian', '2025', 64, 4380000, 206, false, 'Hasil kajian mengenai pola, penyebab, serta kebutuhan layanan bagi korban kekerasan berbasis gender.'],
            ['Materi Pendidikan Kesehatan Ibu dan Anak', 'materi_pendidikan', '2025', 52, 3620000, 318, false, 'Materi edukasi gizi, kesehatan kehamilan, dan perawatan balita untuk kader posyandu.'],
            ['Dokumentasi Perayaan HUT PERWAMA INA ke-28', 'dokumentasi', '2025', 40, 10240000, 189, false, 'Album dokumentasi rangkaian perayaan ulang tahun organisasi ke-28 di Jakarta.'],
        ];

        foreach ($publications as $index => [$title, $category, $year, $pages, $size, $downloads, $featured, $description]) {
            Publication::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'program_id' => Program::query()->inRandomOrder()->value('id'),
                    'title' => $title,
                    'category' => $category,
                    'description' => $description,
                    'file' => $file,
                    'cover_image' => $this->img('publikasi-'.$index, 800, 1100),
                    'year' => $year,
                    'page_count' => $pages,
                    'file_size' => $size,
                    'download_count' => $downloads,
                    'is_featured' => $featured,
                    'published_at' => now()->subDays(15 + ($index * 11)),
                ],
            );
        }
    }

    /**
     * Mitra dan lembaga pendukung program.
     */
    private function seedPartners(): void
    {
        $partners = [
            ['Kementerian Pemberdayaan Perempuan dan Perlindungan Anak', 'lembaga_pemerintah', 'https://www.kemenpppa.go.id', 'Mitra advokasi kebijakan perlindungan perempuan dan anak.'],
            ['Pemerintah Provinsi Maluku', 'lembaga_pemerintah', 'https://malukuprov.go.id', 'Dukungan program pemberdayaan perempuan di wilayah Maluku.'],
            ['Pemerintah Provinsi Maluku Utara', 'lembaga_pemerintah', 'https://malutprov.go.id', 'Kolaborasi program pendidikan dan kesehatan perempuan Maluku Utara.'],
            ['Universitas Pattimura', 'kampus', 'https://unpatti.ac.id', 'Kerja sama beasiswa, penelitian, dan pengabdian masyarakat.'],
            ['Universitas Khairun Ternate', 'kampus', 'https://unkhair.ac.id', 'Mitra pelatihan literasi digital dan kajian gender.'],
            ['Bank Indonesia Perwakilan Maluku', 'lembaga', 'https://www.bi.go.id', 'Pendampingan literasi keuangan serta penguatan UMKM perempuan.'],
            ['Perusahaan Umum Bulog', 'korporasi', 'https://www.bulog.co.id', 'Dukungan logistik kegiatan sosial dan tanggap bencana.'],
            ['Yayasan Bakti Maluku', 'yayasan', 'https://example.org/bakti-maluku', 'Mitra pendanaan program beasiswa dan kesehatan.'],
            ['Komunitas Tenun Maluku', 'komunitas', 'https://example.org/tenun-maluku', 'Pengembangan produk tenun dan pemberdayaan perajin perempuan.'],
            ['PT Pelni', 'korporasi', 'https://www.pelni.co.id', 'Fasilitasi transportasi kegiatan sosial antar pulau.'],
        ];

        foreach ($partners as $index => [$name, $type, $website, $description]) {
            Partner::query()->updateOrCreate(
                ['name' => $name],
                [
                    'logo' => $this->img('mitra-'.$index, 400, 400),
                    'website' => $website,
                    'type' => $type,
                    'description' => $description,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * Anggota organisasi (data keanggotaan).
     */
    private function seedMembers(): void
    {
        $members = [
            ['Rosalina Melatunan', 'Kota Ambon', 'Maluku', 'S1', 'Wiraswasta', 'Ekonomi Kreatif', 'Maluku Tengah', 'active', 2015],
            ['Grace Pattiasina', 'Kota Ambon', 'Maluku', 'S2', 'Dosen', 'Pendidikan', 'Maluku Tenggara', 'active', 2013],
            ['Debora Latuconsina', 'Kota Tual', 'Maluku', 'S1', 'Guru', 'Pendidikan', 'Maluku Tenggara', 'active', 2018],
            ['Silvia Tanamal', 'Kota Ternate', 'Maluku Utara', 'S1', 'Bidan', 'Kesehatan', 'Maluku Tengah', 'active', 2016],
            ['Nurlaila Ahmad', 'Kota Tidore Kepulauan', 'Maluku Utara', 'SMA', 'Pelaku UMKM', 'Ekonomi Kreatif', 'Ternate', 'active', 2020],
            ['Maria Soplanit', 'Cipete Selatan', 'DKI Jakarta', 'S2', 'Peneliti', 'Budaya', 'Maluku Tengah', 'active', 2011],
            ['Johana Talahatu', 'Kota Depok', 'Jawa Barat', 'S3', 'Akademisi', 'Hukum & HAM', 'Maluku Tenggara', 'active', 2012],
            ['Yuliana Renhoran', 'Kota Tual', 'Maluku', 'SMA', 'Perajin Tenun', 'Ekonomi Kreatif', 'Maluku Tenggara', 'active', 2017],
            ['Fitriani Abdulah', 'Kota Sofifi', 'Maluku Utara', 'S1', 'Paralegal', 'Hukum & HAM', 'Halmahera', 'pending', 2026],
            ['Sarah Wael', 'Namlea', 'Maluku', 'S1', 'Guru', 'Lingkungan', 'Buru', 'pending', 2026],
        ];

        foreach ($members as $index => [$name, $city, $province, $education, $occupation, $interest, $origin, $status, $joinedYear]) {
            $organizationId = Organization::query()
                ->where('slug', 'pc-perwama-ina-'.Str::slug($city))
                ->orWhere('slug', 'dpw-perwama-ina-'.Str::slug($province))
                ->value('id');

            Member::query()->updateOrCreate(
                ['member_number' => 'PWI-'.$joinedYear.'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'organization_id' => $organizationId,
                    'region_id' => $this->regionId($city) ?? $this->regionId($province),
                    'full_name' => $name,
                    'birth_place' => $city,
                    'birth_date' => now()->subYears(30 + $index)->startOfYear()->addDays($index * 11)->toDateString(),
                    'gender' => 'P',
                    'address' => 'Jl. Maluku No. '.($index + 1).', '.$city,
                    'city' => $city,
                    'province' => $province,
                    'whatsapp' => '0812'.str_pad((string) (34000000 + $index * 1357), 8, '0', STR_PAD_LEFT),
                    'email' => Str::slug($name, '.').'@contoh.perwama-ina.or.id',
                    'education' => $education,
                    'occupation' => $occupation,
                    'interest_field' => $interest,
                    'maluku_origin' => $origin,
                    'domicile_region' => $province,
                    'photo' => $this->img('anggota-'.$index, 500, 500),
                    'status' => $status,
                    'joined_at' => $joinedYear.'-02-14',
                    'notes' => $status === 'pending' ? 'Menunggu verifikasi berkas oleh pengurus cabang.' : null,
                ],
            );
        }
    }

    /**
     * Label yang dipakai pada berita dan kisah.
     */
    private function seedTags(): void
    {
        $tags = ['Pendidikan', 'Kesehatan', 'Ekonomi Kreatif', 'Literasi Digital', 'Budaya Maluku', 'Perlindungan Perempuan', 'Beasiswa', 'UMKM', 'Lingkungan'];

        foreach ($tags as $index => $name) {
            $tag = Tag::query()->updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]);

            if ($index < 4) {
                $news = News::query()->orderBy('id')->skip($index)->first();

                if ($news) {
                    $news->tags()->syncWithoutDetaching([$tag->getKey()]);
                }
            } else {
                $story = Story::query()->orderBy('id')->skip($index - 4)->first();

                if ($story) {
                    $story->tags()->syncWithoutDetaching([$tag->getKey()]);
                }
            }
        }
    }
}
