<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Hanya pengguna dengan akses admin yang boleh masuk ke panel admin.
     *
     * Jumlah admin tidak dibatasi: setiap pengguna bertanda `is_admin`
     * (kolom "Akses Admin" pada menu Pengguna) dapat mengelola website.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Foto akun yang ditampilkan pada panel admin (topbar, widget akun, dan
     * daftar pengguna). Bila pengguna belum mengunggah foto, Filament akan
     * memakai inisial nama sebagai gantinya.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return media_url($this->photo);
    }

    /**
     * Batasi kueri pada pengguna yang memiliki akses admin.
     */
    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('is_admin', true);
    }

    /**
     * Jumlah admin yang saat ini terdaftar.
     */
    public static function adminCount(): int
    {
        return static::query()->admin()->count();
    }

    /**
     * Benar bila akun ini satu-satunya admin yang tersisa.
     */
    public function isLastAdmin(): bool
    {
        return (bool) $this->is_admin && static::adminCount() <= 1;
    }

    /**
     * Admin terakhir tidak boleh dihapus agar selalu ada pengelola yang dapat
     * masuk ke panel admin (termasuk saat dijalankan dari tinker/CLI).
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user): bool {
            if ($user->isLastAdmin()) {
                return false;
            }

            return true;
        });
    }
}
