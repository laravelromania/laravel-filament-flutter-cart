<?php

declare(strict_types=1);

namespace Modules\Orders\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Modules\Core\ValueObjects\Money;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Filament\Resources\OrderResource\Pages;
use Modules\Orders\Models\Order;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = 'Vânzări';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'comandă';

    protected static ?string $pluralModelLabel = 'comenzi';

    /**
     * Only the mutable, admin-editable fields live on the Edit form. Money,
     * status, addresses and line items are snapshots managed elsewhere (the
     * status via the "schimbă status" action below, never a free-form select).
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('customer_name')->label('Nume client')->required()->maxLength(255),
            TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
            TextInput::make('phone')->label('Telefon')->required()->maxLength(30),
            TextInput::make('awb')
                ->label('AWB')
                ->maxLength(255)
                ->helperText('Se completează automat de modulul Shipping (Partea 10).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Comandă')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => self::asStatus($state)->label())
                    ->color(fn ($state): string => self::asStatus($state)->color()),
                TextColumn::make('created_at')
                    ->label('Plasată')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn (): array => collect(OrderStatus::cases())
                        ->mapWithKeys(fn (OrderStatus $s): array => [$s->value => $s->label()])
                        ->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    static::changeStatusAction(),
                    static::invoiceAction(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * The order detail (View page): items, addresses and totals.
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Comandă')
                ->schema([
                    TextEntry::make('number')->label('Număr'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state): string => self::asStatus($state)->label())
                        ->color(fn ($state): string => self::asStatus($state)->color()),
                    TextEntry::make('created_at')->label('Plasată')->dateTime('d.m.Y H:i'),
                    TextEntry::make('paid_at')->label('Plătită')->dateTime('d.m.Y H:i')->placeholder('—'),
                ])
                ->columns(2),
            Section::make('Client')
                ->schema([
                    TextEntry::make('customer_name')->label('Nume'),
                    TextEntry::make('email')->label('Email'),
                    TextEntry::make('phone')->label('Telefon'),
                ])
                ->columns(3),
            Section::make('Livrare')
                ->schema([
                    TextEntry::make('shipping_label')->label('Metodă'),
                    TextEntry::make('awb')->label('AWB')->placeholder('—'),
                    TextEntry::make('shipping.city')->label('Oraș'),
                    TextEntry::make('shipping.county')->label('Județ'),
                    TextEntry::make('shipping.street')->label('Adresă')->columnSpanFull(),
                ])
                ->columns(2),
            RepeatableEntry::make('items')
                ->label('Produse')
                ->schema([
                    TextEntry::make('name')->label('Produs'),
                    TextEntry::make('quantity')->label('Cantitate'),
                    TextEntry::make('unit_price')
                        ->label('Preț unitar')
                        ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state),
                    TextEntry::make('line_total')
                        ->label('Total linie')
                        ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state),
                ])
                ->columns(4),
            Section::make('Totaluri')
                ->schema([
                    TextEntry::make('items_subtotal')
                        ->label('Subtotal produse')
                        ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state),
                    TextEntry::make('shipping_total')
                        ->label('Livrare')
                        ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state),
                    TextEntry::make('total')
                        ->label('Total')
                        ->formatStateUsing(fn ($state): ?string => $state instanceof Money ? $state->format() : $state),
                ])
                ->columns(3),
        ]);
    }

    /**
     * The "schimbă status" action: a Select whose options are exactly the states
     * the order's current status may transition into, plus an `in` rule so an
     * out-of-graph value is rejected as a validation error. Reused on the View
     * and Edit pages and as a row action.
     */
    public static function changeStatusAction(): Action
    {
        return Action::make('changeStatus')
            ->label('Schimbă status')
            ->icon('heroicon-o-arrow-path')
            ->modalHeading('Schimbă statusul comenzii')
            ->visible(fn (Order $record): bool => $record->status->allowedTransitions() !== [])
            ->schema([
                Select::make('status')
                    ->label('Status nou')
                    ->options(fn (Order $record): array => $record->status->transitions())
                    ->in(fn (Order $record): array => array_keys($record->status->transitions()))
                    ->required(),
            ])
            ->action(function (Order $record, array $data): void {
                $target = OrderStatus::from($data['status']);

                if (! $record->status->canTransitionTo($target)) {
                    return;
                }

                $attributes = ['status' => $target];

                if ($target === OrderStatus::Paid && $record->paid_at === null) {
                    $attributes['paid_at'] = now();
                }

                $record->update($attributes);
            });
    }

    /** A link action that downloads the order's invoice PDF. */
    public static function invoiceAction(): Action
    {
        return Action::make('invoice')
            ->label('Factură PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->url(fn (Order $record): string => route('orders.invoice', ['number' => $record->number]))
            ->openUrlInNewTab();
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Restrict the storefront-managed model updates to staff: the panel is
     * already role-gated, so a plain query scope is enough here.
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin', 'manager']);
    }

    /** Normalise a column/entry state to an OrderStatus enum. */
    private static function asStatus(mixed $state): OrderStatus
    {
        return $state instanceof OrderStatus ? $state : OrderStatus::from((string) $state);
    }
}
