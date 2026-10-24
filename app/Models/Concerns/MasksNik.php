<?php

namespace App\Models\Concerns;

/**
 * Menampilkan NIK tanpa membocorkan seluruh nomor.
 *
 * NIK disimpan terenkripsi, tetapi admin tetap perlu mengenali pemiliknya.
 * Empat digit terakhir cukup untuk pencocokan manual, sedangkan sisa digit
 * diganti titik agar tidak terbaca di layar bersama maupun tangkapan layar.
 */
trait MasksNik
{
    public function maskedNik(): ?string
    {
        $digit = preg_replace('/\D+/', '', (string) $this->nik) ?? '';

        if ($digit === '') {
            return null;
        }

        $tampil = min(4, strlen($digit));

        return str_repeat('•', strlen($digit) - $tampil).substr($digit, -$tampil);
    }
}
