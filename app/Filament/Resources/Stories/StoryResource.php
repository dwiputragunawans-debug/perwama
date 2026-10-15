<?php

namespace App\Filament\Resources\Stories;

use App\Filament\Resources\Stories\Pages\CreateStory;
use App\Filament\Resources\Stories\Pages\EditStory;
use App\Filament\Resources\Stories\Pages\ListStories;
use App\Filament\Resources\Stories\Schemas\StoryForm;
use App\Filament\Resources\Stories\Tables\StoriesTable;
use App\Models\Story;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoryResource extends Resource
{
    protected static ?string $model = Story::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Cerita';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Cerita Perempuan';

    protected static ?string $modelLabel = 'Cerita Perempuan';

    protected static ?string $pluralModelLabel = 'Cerita Perempuan';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return StoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoriesTable::configure($table);
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
            'index' => ListStories::route('/'),
            'create' => CreateStory::route('/create'),
            'edit' => EditStory::route('/{record}/edit'),
        ];
    }
}
