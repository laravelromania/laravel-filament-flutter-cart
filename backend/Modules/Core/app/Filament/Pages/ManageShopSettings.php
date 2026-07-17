<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Modules\Core\Models\Setting;
use UnitEnum;

/**
 * A deliberately tiny settings screen. It edits the handful of store-wide keys
 * that live in the settings table (currency for now) and writes them straight
 * back. No generic typed-settings framework — that would be YAGNI here.
 *
 * @property-read Schema $form
 */
class ManageShopSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected string $view = 'core::filament.pages.manage-shop-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return 'Setări magazin';
    }

    public static function getNavigationLabel(): string
    {
        return 'Setări';
    }

    public function mount(): void
    {
        $this->form->fill([
            'shop_currency' => setting('shop.currency', 'RON'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('shop_currency')
                    ->label('Monedă (cod ISO 4217)')
                    ->helperText('Seria folosește o singură monedă. Implicit RON.')
                    ->required()
                    ->maxLength(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::query()->updateOrCreate(
            ['key' => 'shop.currency'],
            ['value' => strtoupper((string) $data['shop_currency'])],
        );

        Notification::make()
            ->title('Setările au fost salvate.')
            ->success()
            ->send();
    }
}
