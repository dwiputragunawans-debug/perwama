<?php

namespace App\Filament\Resources\ProgramCategories\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\ProgramCategories\ProgramCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditProgramCategory extends EditRecord
{
    protected static string $resource = ProgramCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
