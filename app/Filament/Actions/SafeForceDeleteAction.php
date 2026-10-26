<?php

namespace App\Filament\Actions;

use App\Support\ForeignKeyConstraint;
use Filament\Actions\ForceDeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Hapus permanen yang menampilkan pesan ramah bila foreign key menolak.
 *
 * Soft delete tidak menghapus baris, sehingga penghapusan permanen tetap dapat
 * gagal karena relasi `RESTRICT`. Tanpa penanganan ini, admin akan melihat
 * halaman error mentah dari basis data.
 */
class SafeForceDeleteAction extends ForceDeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (): void {
            try {
                $result = $this->process(static fn (Model $record): ?bool => $record->forceDelete());
            } catch (QueryException $exception) {
                if (! ForeignKeyConstraint::violated($exception)) {
                    throw $exception;
                }

                $this->failureNotificationTitle('Data tidak dapat dihapus permanen');
                $this->failureNotificationBody(ForeignKeyConstraint::USER_MESSAGE);
                $this->failure();

                return;
            }

            $result ? $this->success() : $this->failure();
        });
    }
}
