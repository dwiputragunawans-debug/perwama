<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    /**
     * Log dibuat otomatis oleh AuditObserver; tidak ada tombol tambah.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
