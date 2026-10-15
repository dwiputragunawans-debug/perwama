<?php

namespace App\Filament\Resources\PartnershipRequests\Pages;

use App\Filament\Resources\PartnershipRequests\PartnershipRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnershipRequest extends EditRecord
{
    protected static string $resource = PartnershipRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
