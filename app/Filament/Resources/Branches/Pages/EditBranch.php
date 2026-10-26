<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\Branches\BranchResource;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
