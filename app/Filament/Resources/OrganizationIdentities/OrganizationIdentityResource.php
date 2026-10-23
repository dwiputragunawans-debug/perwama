<?php

namespace App\Filament\Resources\OrganizationIdentities;

use App\Filament\Resources\OrganizationIdentities\Pages\EditOrganizationIdentity;
use App\Filament\Resources\OrganizationIdentities\Pages\ListOrganizationIdentities;
use App\Filament\Resources\OrganizationIdentities\Schemas\OrganizationIdentityForm;
use App\Filament\Resources\OrganizationIdentities\Tables\OrganizationIdentitiesTable;
use App\Models\OrganizationIdentity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrganizationIdentityResource extends Resource
{
    protected static ?string $model = OrganizationIdentity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Identitas Organisasi';

    protected static ?string $modelLabel = 'Identitas Organisasi';

    protected static ?string $pluralModelLabel = 'Identitas Organisasi';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Identitas organisasi bersifat tunggal (satu baris data).
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationIdentityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationIdentitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationIdentities::route('/'),
            'edit' => EditOrganizationIdentity::route('/{record}/edit'),
        ];
    }
}
