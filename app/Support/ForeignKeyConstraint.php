<?php

namespace App\Support;

use Illuminate\Database\QueryException;

/**
 * Mengenali kegagalan hapus karena foreign key masih dipakai tabel lain.
 *
 * Pesan mentah dari MySQL/PostgreSQL/SQLite berbeda-beda dan tidak layak
 * ditampilkan ke pengelola. Kelas ini menyatukan deteksinya agar panel admin
 * dapat menampilkan satu pesan yang mudah dipahami.
 */
final class ForeignKeyConstraint
{
    public const USER_MESSAGE = 'Data ini masih digunakan oleh data lain. Hapus atau pindahkan data terkait terlebih dahulu, lalu coba lagi.';

    public static function violated(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $driverCode = (string) ($exception->errorInfo[1] ?? '');
        $message = strtolower($exception->getMessage());

        return $sqlState === '23503' // PostgreSQL: foreign_key_violation
            || in_array($driverCode, ['1451', '547'], true) // MySQL: child row exists; SQL Server: reference constraint
            || str_contains($message, 'foreign key')
            || str_contains($message, 'reference constraint');
    }
}
