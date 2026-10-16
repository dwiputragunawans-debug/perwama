<?php

namespace App\Filament\Resources\OrganizationProfiles;

use App\Filament\Resources\OrganizationProfiles\Pages\CreateOrganizationProfile;
use App\Filament\Resources\OrganizationProfiles\Pages\EditOrganizationProfile;
use App\Filament\Resources\OrganizationProfiles\Pages\ListOrganizationProfiles;
use App\Filament\Resources\OrganizationProfiles\Schemas\OrganizationProfileForm;
use App\Filament\Resources\OrganizationProfiles\Tables\OrganizationProfilesTable;
use App\Models\OrganizationProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use UnitEnum;

class OrganizationProfileResource extends Resource
{
    protected static ?string $model = OrganizationProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Profil Organisasi';

    protected static ?string $modelLabel = 'Profil Organisasi';

    protected static ?string $pluralModelLabel = 'Profil Organisasi';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Profil organisasi bersifat tunggal (satu baris data): begitu datanya ada,
     * akses membuat record baru ditolak. Filament memakai respons ini untuk
     * menyembunyikan tombol "Buat Profil Organisasi" pada halaman daftar,
     * sekaligus menolak halaman /create — `Resource::canCreate()` bawaan
     * memang diturunkan dari respons ini, jadi tidak perlu ditimpa terpisah.
     *
     * Pemeriksaan kebijakan (policy) bawaan tetap dijalankan selama tabel masih
     * kosong, sehingga aturan tambahan dari policy tidak hilang.
     */
    public static function getCreateAuthorizationResponse(): Response
    {
        if (OrganizationProfile::query()->exists()) {
            return Response::deny();
        }

        return parent::getCreateAuthorizationResponse();
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationProfilesTable::configure($table);
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
            'index' => ListOrganizationProfiles::route('/'),
            'create' => CreateOrganizationProfile::route('/create'),
            'edit' => EditOrganizationProfile::route('/{record}/edit'),
        ];
    }
}
