<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    protected static ?string $title = 'Valori';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('value')
                ->label('Valoare')
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
                ->helperText('Unic în cadrul acestui atribut (nu global).'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                TextColumn::make('value')
                    ->label('Valoare')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
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
            ->defaultSort('value');
    }
}
