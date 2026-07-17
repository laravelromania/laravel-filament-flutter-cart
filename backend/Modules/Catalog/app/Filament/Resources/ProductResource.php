<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Catalog\Filament\Resources\ProductResource\Pages;
use Modules\Catalog\Models\Product;
use Modules\Core\ValueObjects\Money;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'produs';

    protected static ?string $pluralModelLabel = 'produse';

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
            Textarea::make('description')
                ->label('Descriere')
                ->rows(4)
                ->columnSpanFull(),
            Select::make('brand_id')
                ->label('Brand')
                ->relationship('brand', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Select::make('categories')
                ->label('Categorii')
                ->relationship('categories', 'name')
                ->multiple()
                ->searchable()
                ->preload(),
            TextInput::make('price')
                ->label('Preț (lei)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step('0.01')
                ->prefix('RON')
                ->helperText('Introdus în lei; stocat intern în bani (unități minore).')
                // La editare `price` vine ca Money (din MoneyCast) -> afișăm în lei.
                ->formatStateUsing(fn ($state): ?string => $state instanceof Money
                    ? number_format($state->getMinorAmount() / 100, 2, '.', '')
                    : $state)
                // Leii tastați -> bani; MoneyCast::set acceptă un int.
                ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
            Toggle::make('is_active')
                ->label('Activ')
                ->default(true),
            SpatieMediaLibraryFileUpload::make('images')
                ->label('Imagini')
                ->collection('images')
                ->image()
                ->multiple()
                ->reorderable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label('Imagine')
                    ->collection('images')
                    ->circular()
                    ->limit(1),
                TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Preț')
                    // `price` este un Money (cast) -> îl formatăm cu ->format().
                    ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state)
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Activ'),
            ])
            ->filters([
                SelectFilter::make('brand')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('name');
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
