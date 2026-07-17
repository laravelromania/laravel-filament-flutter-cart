<?php

declare(strict_types=1);

namespace Modules\Checkout\Services;

use InvalidArgumentException;
use Modules\Core\Contracts\ShippingProvider;

/**
 * A registry of shipping drivers keyed by their {@see ShippingProvider::code()}.
 * Checkout registers the {@see \Modules\Checkout\Drivers\MockShippingProvider}
 * here in Part 8; the Shipping module (Part 10) will resolve this same singleton
 * in its own service provider and `register()` the real Sameday/Cargus drivers,
 * so they appear at checkout without Checkout ever knowing they exist.
 */
class ShippingManager
{
    /** @var array<string, ShippingProvider> */
    private array $drivers = [];

    public function register(ShippingProvider $driver): void
    {
        $this->drivers[$driver->code()] = $driver;
    }

    /**
     * @return ShippingProvider[]
     */
    public function available(): array
    {
        return array_values($this->drivers);
    }

    public function has(string $code): bool
    {
        return isset($this->drivers[$code]);
    }

    public function get(string $code): ShippingProvider
    {
        return $this->drivers[$code]
            ?? throw new InvalidArgumentException("Metodă de livrare necunoscută: {$code}.");
    }
}
