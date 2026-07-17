<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Catalog\Filament\Resources\CategoryResource\Pages;
use Modules\Catalog\Models\Category;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'categorie';

    protected static ?string $pluralModelLabel = 'categorii';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nume')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, ?string $state, Set $set) => $operation === 'create'
                    ? $set('slug', Str::slug((string) $state))
                    : null),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Select::make('parent_id')
                ->label('Categorie părinte')
                ->relationship(
                    name: 'parent',
                    titleAttribute: 'name',
                    // O categorie nu poate fi propriul părinte.
                    modifyQueryUsing: fn (Builder $query, ?Category $record) => $query
                        ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey())),
                )
                ->searchable()
                ->preload()
                ->nullable(),
            TextInput::make('position')
                ->label('Poziție')
                ->numeric()
                ->default(0)
                ->required()
                ->helperText('Ordonează categoriile în cadrul aceluiași părinte.'),
            Toggle::make('is_active')
                ->label('Activ')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Părinte')
                    ->placeholder('— rădăcină —')
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Poziție')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Activ'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Activ'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position')
            ->reorderable('position');
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
