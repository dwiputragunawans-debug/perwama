<?php

namespace App\Filament\Resources\Statistics\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Statistics\StatisticResource;
use Filament\Resources\Pages\EditRecord;

class EditStatistic extends EditRecord
{
    protected static string $resource = StatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
