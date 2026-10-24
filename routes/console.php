<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Retensi data pribadi pendaftar (SEC-04): pendaftaran yang ditolak
 * dianonimkan otomatis setelah masa retensi. Jalankan manual dengan
 * `php artisan membership:purge-rejected` bila perlu.
 */
Schedule::command('membership:purge-rejected')->dailyAt('02:00');
