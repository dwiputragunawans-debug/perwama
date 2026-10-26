<?php

namespace App\Filament\Resources\OrganizationFigures\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\OrganizationFigures\OrganizationFigureResource;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationFigure extends EditRecord
{
    protected static string $resource = OrganizationFigureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
