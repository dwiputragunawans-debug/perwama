<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Tombol hapus mengikuti UserPolicy: akun sendiri dan admin terakhir
     * tidak dapat dihapus.
     */
    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }

    /**
     * Filament sudah mencegah field yang di-disable agar tidak tersimpan,
     * tetapi permintaan dari sisi klien masih dapat dimanipulasi. Karena itu
     * penguncian akses admin akun sendiri ditegakkan ulang di sisi server.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->is(auth()->user())) {
            $data['is_admin'] = true;
        }

        return $data;
    }
}
