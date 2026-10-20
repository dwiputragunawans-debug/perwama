<?php

use App\Models\Activity;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Publication;
use App\Models\Story;

/**
 * Skrip sekali pakai untuk merapikan resource Filament hasil `make:filament-resource --generate`.
 * Jalankan: php database/seeders/support/filament_customize.php
 */
$root = dirname(__DIR__, 3);
$base = $root.'/app/Filament/Resources';

require $root.'/vendor/autoload.php';

/*
 * Grup navigasi mengikuti menu pada website (Beranda, Tentang, Program,
 * Wilayah, Kegiatan, Cerita, Berita, Galeri, Kontak) ditambah "Bergabung"
 * dan "Pengaturan". Urutan grupnya didefinisikan pada
 * App\Providers\Filament\AdminPanelProvider::navigationGroups().
 */
$nav = [
    'OrganizationProfiles' => ['Tentang', 1, 'OutlinedBuildingOffice2', 'Profil Organisasi', 'Profil Organisasi', 'Profil Organisasi', 'name'],
    'OrganizationHistories' => ['Tentang', 2, 'OutlinedClock', 'Sejarah', 'Sejarah', 'Sejarah', 'title'],
    'Management' => ['Tentang', 3, 'OutlinedUserGroup', 'Pengurus', 'Pengurus', 'Pengurus', 'name'],
    'Partners' => ['Tentang', 4, 'OutlinedHandRaised', 'Mitra & Jejaring', 'Mitra', 'Mitra & Jejaring', 'name'],
    'Programs' => ['Program', 1, 'OutlinedRectangleStack', 'Program', 'Program', 'Program', 'title'],
    'ProgramCategories' => ['Program', 2, 'OutlinedTag', 'Kategori Program', 'Kategori Program', 'Kategori Program', 'name'],
    'Regions' => ['Wilayah', 1, 'OutlinedGlobeAsiaAustralia', 'Wilayah', 'Wilayah', 'Wilayah', 'name'],
    'Organizations' => ['Wilayah', 2, 'OutlinedBuildingLibrary', 'Organisasi / Cabang', 'Organisasi', 'Organisasi', 'name'],
    'Branches' => ['Wilayah', 3, 'OutlinedMapPin', 'Cabang', 'Cabang', 'Cabang', 'name'],
    'Activities' => ['Kegiatan', 1, 'OutlinedSparkles', 'Kegiatan', 'Kegiatan', 'Kegiatan', 'title'],
    'Events' => ['Kegiatan', 2, 'OutlinedCalendarDays', 'Agenda', 'Agenda', 'Agenda', 'title'],
    'Stories' => ['Cerita', 1, 'OutlinedBookOpen', 'Cerita Perempuan', 'Cerita Perempuan', 'Cerita Perempuan', 'title'],
    'News' => ['Berita', 1, 'OutlinedNewspaper', 'Berita', 'Berita', 'Berita', 'title'],
    'Publications' => ['Berita', 2, 'OutlinedDocumentText', 'Publikasi', 'Publikasi', 'Publikasi', 'title'],
    'Galleries' => ['Galeri', 1, 'OutlinedPhoto', 'Galeri', 'Galeri', 'Galeri', 'title'],
    'GalleryItems' => ['Galeri', 2, 'OutlinedSquare2Stack', 'Item Galeri', 'Item Galeri', 'Item Galeri', 'caption'],
    'Videos' => ['Galeri', 3, 'OutlinedVideoCamera', 'Video', 'Video', 'Video', 'title'],
    'MembershipApplications' => ['Bergabung', 1, 'OutlinedUserPlus', 'Pendaftar Anggota', 'Pendaftar Anggota', 'Pendaftar Anggota', 'full_name'],
    'Members' => ['Bergabung', 2, 'OutlinedIdentification', 'Anggota', 'Anggota', 'Anggota', 'full_name'],
    'PartnershipRequests' => ['Bergabung', 3, 'OutlinedHandThumbUp', 'Permohonan Kerja Sama', 'Permohonan', 'Permohonan Kerja Sama', 'name'],
    'ContactMessages' => ['Kontak', 1, 'OutlinedEnvelope', 'Pesan Kontak', 'Pesan Kontak', 'Pesan Kontak', 'name'],
    'Statistics' => ['Tentang', 5, 'OutlinedChartBar', 'Statistik', 'Statistik', 'Statistik', 'label'],
    'Tags' => ['Berita', 3, 'OutlinedTag', 'Tag', 'Tag', 'Tag', 'name'],
    'Media' => ['Pengaturan', 3, 'OutlinedFolder', 'Media', 'Media', 'Media', 'name'],
    'SeoMetadata' => ['Pengaturan', 4, 'OutlinedMagnifyingGlass', 'SEO', 'SEO', 'SEO', 'meta_title'],
    'AuditLogs' => ['Pengaturan', 5, 'OutlinedClipboardDocumentList', 'Log Aktivitas', 'Log Aktivitas', 'Log Aktivitas', 'action'],
    'Users' => ['Pengaturan', 6, 'OutlinedUsers', 'Pengguna', 'Pengguna', 'Pengguna', 'name'],
];

// Opsi enum per "<Folder>:<field>".
$enums = [
    'Programs:type' => ['kerja_anggaran' => 'Program Kerja & Anggaran', 'lainnya' => 'Program Lainnya'],
    'Programs:status' => ['planned' => 'Direncanakan', 'ongoing' => 'Berjalan', 'completed' => 'Selesai'],
    'News:type' => ['berita' => 'Berita', 'artikel' => 'Artikel', 'opini' => 'Opini', 'pengumuman' => 'Pengumuman'],
    'News:category' => News::CATEGORIES,
    'Activities:category' => Activity::CATEGORIES,
    'Stories:category' => Story::CATEGORIES,
    'Publications:category' => Publication::CATEGORIES,
    'Galleries:album_category' => Gallery::ALBUMS,
    'Events:status' => ['upcoming' => 'Akan Datang', 'ongoing' => 'Berlangsung', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'],
    'Members:status' => ['pending' => 'Menunggu', 'active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'rejected' => 'Ditolak'],
    'MembershipApplications:status' => ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'],
    'MembershipApplications:membership_type' => ['anggota' => 'Anggota', 'relawan' => 'Relawan', 'mitra' => 'Mitra', 'kolaborasi' => 'Kolaborasi'],
    'PartnershipRequests:type' => ['anggota' => 'Menjadi Anggota', 'relawan' => 'Menjadi Relawan', 'mitra' => 'Menjadi Mitra', 'kolaborasi' => 'Berkolaborasi'],
    'PartnershipRequests:status' => ['pending' => 'Menunggu', 'processed' => 'Diproses', 'rejected' => 'Ditolak'],
    'Organizations:type' => ['dpp' => 'Dewan Pimpinan Pusat (DPP)', 'dpw' => 'Dewan Pimpinan Wilayah (DPW)', 'pc' => 'Pimpinan Cabang (PC)'],
    'Branches:type' => ['ranting' => 'Ranting', 'cabang' => 'Cabang', 'wilayah' => 'Wilayah'],
    'Regions:type' => ['province' => 'Provinsi', 'regency' => 'Kabupaten', 'city' => 'Kota'],
    'Management:level' => ['dpp' => 'DPP (Pusat)', 'dpw' => 'DPW (Wilayah)', 'pc' => 'PC (Cabang)'],
    'GalleryItems:type' => ['photo' => 'Foto', 'video' => 'Video'],
    'Members:gender' => ['Perempuan' => 'Perempuan', 'Laki-laki' => 'Laki-laki'],
    'MembershipApplications:gender' => ['Perempuan' => 'Perempuan', 'Laki-laki' => 'Laki-laki'],
];

$relationshipNames = [
    'program_category_id' => 'category',
    'reviewed_by' => 'reviewer',
    'parent_id' => 'parent',
];

// ---- Tambahkan konfigurasi navigasi ke setiap Resource ----
foreach ($nav as $folder => [$group, $sort, $icon, $label, $singular, $plural, $titleAttr]) {
    $resourceFile = null;
    foreach (glob($base.'/'.$folder.'/*Resource.php') as $candidate) {
        $resourceFile = $candidate;

        break;
    }

    if (! $resourceFile || ! is_file($resourceFile)) {
        echo "SKIP resource: $folder\n";

        continue;
    }

    $content = file_get_contents($resourceFile);

    if (! str_contains($content, 'use UnitEnum;')) {
        $content = preg_replace('/^(use BackedEnum;)$/m', "use BackedEnum;\nuse UnitEnum;", $content, 1);
    }

    $props = "protected static string|UnitEnum|null \$navigationGroup = '".$group."';\n\n"
        .'    protected static ?int $navigationSort = '.$sort.";\n\n"
        ."    protected static ?string \$navigationLabel = '".addslashes($label)."';\n\n"
        ."    protected static ?string \$modelLabel = '".addslashes($singular)."';\n\n"
        ."    protected static ?string \$pluralModelLabel = '".addslashes($plural)."';\n\n"
        ."    protected static ?string \$recordTitleAttribute = '".$titleAttr."';";

    $content = preg_replace(
        '/protected static string\|BackedEnum\|null \$navigationIcon = [^;]+;/',
        "\$0\n\n    ".$props,
        $content,
        1
    );

    $content = preg_replace(
        '/protected static string\|BackedEnum\|null \$navigationIcon = [^;]+;/',
        'protected static string|BackedEnum|null $navigationIcon = Heroicon::'.$icon.';',
        $content,
        1
    );

    file_put_contents($resourceFile, $content);
    echo "OK resource: $folder\n";
}

// ---- Perbaikan form ----
foreach (glob($base.'/*/Schemas/*.php') as $formFile) {
    $folder = basename(dirname(dirname($formFile)));
    $lines = file($formFile);
    $out = [];
    $needsSelect = false;
    $needsRich = false;
    $skip = false;

    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];

        if ($skip && preg_match('/^\s*->/', $line)) {
            continue;
        }
        $skip = false;

        // TextInput enum -> Select
        if (preg_match("/TextInput::make\('(\w+)'\)/", $line, $m) && isset($enums[$folder.':'.$m[1]])) {
            $field = $m[1];
            $opts = $enums[$folder.':'.$field];
            $default = '';
            for ($j = $i + 1; $j < count($lines) && preg_match('/^\s*->/', $lines[$j]); $j++) {
                if (preg_match("/default\('([^']+)'\)/", $lines[$j], $dm)) {
                    $default = $dm[1];
                }
            }
            $out[] = "                Select::make('{$field}')\n";
            $out[] = "                    ->options([\n";
            foreach ($opts as $k => $v) {
                $out[] = "                        '".$k."' => '".addslashes($v)."',\n";
            }
            $out[] = "                    ])\n";
            if ($default !== '' && isset($opts[$default])) {
                $out[] = "                    ->default('".$default."')\n";
            }
            $out[] = "                    ->required(),\n";
            $needsSelect = true;
            $skip = true;

            continue;
        }

        // TextInput('x_id') -> Select relationship
        if (preg_match("/TextInput::make\('(\w+)_id'\)/", $line, $m) && $m[1] !== 'model') {
            $field = $m[1].'_id';
            $rel = $relationshipNames[$field] ?? $m[1];
            $out[] = "                Select::make('{$field}')\n";
            $out[] = "                    ->relationship('{$rel}', 'name')\n";
            $out[] = "                    ->searchable()\n";
            $out[] = "                    ->preload(),\n";
            $needsSelect = true;
            $skip = true;

            continue;
        }

        // TextInput('reviewed_by') -> Select user
        if (preg_match("/TextInput::make\('reviewed_by'\)/", $line)) {
            $out[] = "                Select::make('reviewed_by')\n";
            $out[] = "                    ->relationship('reviewer', 'name')\n";
            $out[] = "                    ->searchable()\n";
            $out[] = "                    ->preload(),\n";
            $needsSelect = true;
            $skip = true;

            continue;
        }

        // Slug opsional
        if (preg_match("/TextInput::make\('slug'\)/", $line)) {
            $out[] = "                TextInput::make('slug')\n";
            $out[] = "                    ->maxLength(255)\n";
            $out[] = "                    ->unique(ignoreRecord: true)\n";
            $out[] = "                    ->helperText('Kosongkan untuk dibuat otomatis.'),\n";
            $skip = true;

            continue;
        }

        // RichEditor untuk teks panjang
        if (preg_match("/Textarea::make\('(content|description|journey|activities|impact)'\)/", $line, $m)) {
            $out[] = "                RichEditor::make('".$m[1]."')\n";
            $out[] = "                    ->columnSpanFull(),\n";
            $needsRich = true;
            $skip = true;

            continue;
        }

        // FileUpload -> disk publik
        if (preg_match("/FileUpload::make\('(\w+)'\)/", $line, $m)) {
            $field = $m[1];
            $isImage = false;
            for ($j = $i + 1; $j < count($lines) && preg_match('/^\s*->/', $lines[$j]); $j++) {
                if (str_contains($lines[$j], 'image()')) {
                    $isImage = true;
                }
            }
            $dir = 'uploads/'.$folder;
            $out[] = "                FileUpload::make('{$field}')\n";
            if ($isImage) {
                $out[] = "                    ->image()\n";
            }
            $out[] = "                    ->disk('public')\n";
            $out[] = "                    ->directory('{$dir}')\n";
            if (preg_match('/logo|photo|poster|cover|thumbnail|image/', $field)) {
                $out[] = "                    ->imageEditor()\n";
            }
            $out[] = "                    ->maxSize(10240),\n";
            $skip = true;

            continue;
        }

        $out[] = $line;
    }

    $content = implode('', $out);

    if ($needsSelect && ! str_contains($content, 'use Filament\Forms\Components\Select;')) {
        $content = preg_replace('/^(use Filament\\\\Forms\\\\Components\\\\[A-Za-z]+;)$/m', "use Filament\\Forms\\Components\\Select;\n$1", $content, 1);
    }

    if ($needsRich && ! str_contains($content, 'use Filament\Forms\Components\RichEditor;')) {
        $content = preg_replace('/^(use Filament\\\\Forms\\\\Components\\\\[A-Za-z]+;)$/m', "use Filament\\Forms\\Components\\RichEditor;\n$1", $content, 1);
    }

    file_put_contents($formFile, $content);
    echo 'OK form: '.basename($formFile)."\n";
}

echo "\nSelesai.\n";
