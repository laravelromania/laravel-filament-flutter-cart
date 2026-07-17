<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\ValueObjects\Money;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variante';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sku')
                ->label('SKU')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->helperText('Cod unic de produs (identifică varianta la comandă/stoc).'),
            TextInput::make('price')
                ->label('Preț (lei, opțional)')
                // La fel ca pe ProductResource: NU ->numeric(), ca să nu se
                // înregistreze automat un NumberStateCast care crapă la
                // hidratarea unui preț existent (Money) sau a lui null.
                ->type('number')
                ->inputMode('decimal')
                ->rule('numeric')
                ->minValue(0)
                ->step('0.01')
                ->prefix('RON')
                ->nullable()
                ->helperText('Lăsat gol -> se folosește prețul produsului (effectivePrice()).')
                ->formatStateUsing(fn ($state): ?string => $state instanceof Money
                    ? number_format($state->getMinorAmount() / 100, 2, '.', '')
                    : $state)
                ->dehydrateStateUsing(fn ($state): ?int => filled($state) ? (int) round(((float) $state) * 100) : null),
            TextInput::make('stock')
                ->label('Stoc')
                ->required()
                ->integer()
                ->minValue(0)
                ->default(0),
            Select::make('attributeValues')
                ->label('Valori atribut')
                ->relationship('attributeValues', 'value')
                ->multiple()
                ->searchable()
                ->preload()
                ->helperText('Combinația de valori (ex: Roșu + M) care definește această variantă.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Preț')
                    ->placeholder('— (preț produs)')
                    ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state),
                TextColumn::make('stock')
                    ->label('Stoc')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('attributeValues.value')
                    ->label('Atribute')
                    ->badge()
                    ->separator(','),
            ])
            ->headerActions([
                CreateAction::make(),
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
            ->defaultSort('id');
    }
}
