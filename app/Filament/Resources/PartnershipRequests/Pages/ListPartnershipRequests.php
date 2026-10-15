<?php

namespace App\Filament\Resources\PartnershipRequests\Pages;

use App\Filament\Resources\PartnershipRequests\PartnershipRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnershipRequests extends ListRecords
{
    protected static string $resource = PartnershipRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
