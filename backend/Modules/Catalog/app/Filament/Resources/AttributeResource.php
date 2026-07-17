<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Catalog\Filament\Resources\AttributeResource\Pages;
use Modules\Catalog\Filament\Resources\AttributeResource\RelationManagers\ValuesRelationManager;
use Modules\Catalog\Models\Attribute;
use UnitEnum;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'atribut';

    protected static ?string $pluralModelLabel = 'atribute';

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
                ->unique(ignoreRecord: true)
                ->helperText('Generat din nume; editabil, trebuie unic.'),
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
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('values_count')
                    ->label('Valori')
                    ->counts('values')
                    ->badge(),
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
            ->defaultSort('name');
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
