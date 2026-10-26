<?php

namespace App\Filament\Actions;

use App\Support\ForeignKeyConstraint;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Throwable;

/**
 * Hapus massal yang tetap menyelesaikan record lain bila satu record ditolak
 * foreign key. Record yang masih dipakai dilaporkan sebagai kegagalan dengan
 * pesan yang dapat dipahami, bukan exception mentah dari basis data.
 */
class SafeDeleteBulkAction extends DeleteBulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (): void {
            $this->process(function (DeleteBulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                if (! $action->shouldFetchSelectedRecords()) {
                    try {
                        $action->reportBulkProcessingSuccessfulRecordsCount(
                            $action->getSelectedRecordsQuery()->delete(),
                        );
                    } catch (Throwable $exception) {
                        $isForeignKeyViolation = $exception instanceof QueryException
                            && ForeignKeyConstraint::violated($exception);

                        if (! $isForeignKeyViolation) {
                            report($exception);
                        }

                        $action->reportCompleteBulkProcessingFailure(
                            $isForeignKeyViolation ? 'relasi_masih_digunakan' : null,
                            $isForeignKeyViolation ? ForeignKeyConstraint::USER_MESSAGE : null,
                        );
                    }

                    return;
                }

                $isFirstUnexpectedException = true;

                foreach ($records as $record) {
                    try {
                        $record->delete() || $action->reportBulkProcessingFailure();
                    } catch (Throwable $exception) {
                        if ($exception instanceof QueryException && ForeignKeyConstraint::violated($exception)) {
                            $action->reportBulkProcessingFailure(
                                'relasi_masih_digunakan',
                                ForeignKeyConstraint::USER_MESSAGE,
                            );
                        } else {
                            $action->reportBulkProcessingFailure();

                            if ($isFirstUnexpectedException) {
                                report($exception);
                                $isFirstUnexpectedException = false;
                            }
                        }
                    }
                }
            });
        });
    }
}
