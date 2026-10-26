<?php

namespace App\Filament\Actions;

use App\Support\ForeignKeyConstraint;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Tombol hapus tunggal yang menampilkan pesan ramah saat foreign key menolak
 * penghapusan. Tanpa ini, Filament meneruskan QueryException ke halaman error
 * sehingga pengelola tidak tahu bahwa data masih dipakai.
 */
class SafeDeleteAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (): void {
            try {
                $result = $this->process(static fn (Model $record): ?bool => $record->delete());
            } catch (QueryException $exception) {
                if (! ForeignKeyConstraint::violated($exception)) {
                    throw $exception;
                }

                $this->failureNotificationTitle('Data tidak dapat dihapus');
                $this->failureNotificationBody(ForeignKeyConstraint::USER_MESSAGE);
                $this->failure();

                return;
            }

            $result ? $this->success() : $this->failure();
        });
    }
}
