<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Menyimpan teks sensitif dalam bentuk terenkripsi (AES-256 via APP_KEY).
 *
 * Nilai lama yang masih tersimpan sebagai teks biasa tetap dapat dibaca agar
 * data yang sudah ada tidak hilang; saat record disimpan kembali, nilainya
 * otomatis dienkripsi. Kolom terenkripsi tidak dapat dicari dengan LIKE,
 * sehingga tampilan admin memakai NIK bertopeng (lihat MasksNik).
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class EncryptedString implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (DecryptException) {
            // Data lama sebelum enkripsi diterapkan.
            return (string) $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString((string) $value);
    }
}
