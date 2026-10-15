<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Daftar pengguna hanya dapat dilihat oleh admin.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, User $model): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Admin boleh menambah pengelola baru; jumlah admin tidak dibatasi.
     */
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, User $model): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Akun sendiri dan admin terakhir tidak boleh dihapus, agar tidak ada
     * pengelola yang terkunci dari panel admin.
     */
    public function delete(User $user, User $model): bool
    {
        return ! $user->is($model) && ! $model->isLastAdmin();
    }

    /**
     * Tombol hapus massal tetap tersedia, tetapi setiap akun diperiksa satu
     * per satu memakai `delete()` melalui `authorizeIndividualRecords()`.
     */
    public function deleteAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
