<?php

namespace App\Filament\Resources\OrganizationFigures\Pages;

use App\Filament\Resources\OrganizationFigures\OrganizationFigureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationFigures extends ListRecords
{
    protected static string $resource = OrganizationFigureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
