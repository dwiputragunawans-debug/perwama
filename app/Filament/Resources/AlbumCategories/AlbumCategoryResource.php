<?php

namespace App\Filament\Resources\AlbumCategories;

use App\Filament\Resources\AlbumCategories\Pages\CreateAlbumCategory;
use App\Filament\Resources\AlbumCategories\Pages\EditAlbumCategory;
use App\Filament\Resources\AlbumCategories\Pages\ListAlbumCategories;
use App\Filament\Resources\AlbumCategories\Schemas\AlbumCategoryForm;
use App\Filament\Resources\AlbumCategories\Tables\AlbumCategoriesTable;
use App\Models\AlbumCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AlbumCategoryResource extends Resource
{
    protected static ?string $model = AlbumCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Galeri';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Kategori Album';

    protected static ?string $modelLabel = 'Kategori Album';

    protected static ?string $pluralModelLabel = 'Kategori Album';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AlbumCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlbumCategoriesTable::configure($table);
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
            'index' => ListAlbumCategories::route('/'),
            'create' => CreateAlbumCategory::route('/create'),
            'edit' => EditAlbumCategory::route('/{record}/edit'),
        ];
    }
}
