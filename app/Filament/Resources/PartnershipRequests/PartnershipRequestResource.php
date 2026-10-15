<?php

namespace App\Filament\Resources\PartnershipRequests;

use App\Filament\Resources\PartnershipRequests\Pages\CreatePartnershipRequest;
use App\Filament\Resources\PartnershipRequests\Pages\EditPartnershipRequest;
use App\Filament\Resources\PartnershipRequests\Pages\ListPartnershipRequests;
use App\Filament\Resources\PartnershipRequests\Schemas\PartnershipRequestForm;
use App\Filament\Resources\PartnershipRequests\Tables\PartnershipRequestsTable;
use App\Models\PartnershipRequest;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PartnershipRequestResource extends Resource
{
    protected static ?string $model = PartnershipRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandThumbUp;

    protected static string|UnitEnum|null $navigationGroup = 'Bergabung';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Permohonan Kerja Sama';

    protected static ?string $modelLabel = 'Permohonan';

    protected static ?string $pluralModelLabel = 'Permohonan Kerja Sama';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PartnershipRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnershipRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartnershipRequests::route('/'),
            'create' => CreatePartnershipRequest::route('/create'),
            'edit' => EditPartnershipRequest::route('/{record}/edit'),
        ];
    }
}
