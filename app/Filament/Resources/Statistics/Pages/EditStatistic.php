<?php

namespace App\Filament\Resources\Statistics\Pages;

use App\Filament\Resources\Statistics\StatisticResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStatistic extends EditRecord
{
    protected static string $resource = StatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
