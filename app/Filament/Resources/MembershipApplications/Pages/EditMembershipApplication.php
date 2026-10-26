<?php

namespace App\Filament\Resources\MembershipApplications\Pages;

use App\Filament\Actions\SafeDeleteAction;
use App\Filament\Resources\MembershipApplications\MembershipApplicationResource;
use Filament\Resources\Pages\EditRecord;

class EditMembershipApplication extends EditRecord
{
    protected static string $resource = MembershipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SafeDeleteAction::make(),
        ];
    }
}
