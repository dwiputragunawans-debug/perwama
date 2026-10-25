<?php

namespace App\Filament\Resources\OrganizationFigures\Pages;

use App\Filament\Resources\OrganizationFigures\OrganizationFigureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationFigure extends EditRecord
{
    protected static string $resource = OrganizationFigureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
