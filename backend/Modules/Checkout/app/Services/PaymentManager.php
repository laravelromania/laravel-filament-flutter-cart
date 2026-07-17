<?php

declare(strict_types=1);

namespace Modules\Checkout\Services;

use InvalidArgumentException;
use Modules\Core\Contracts\PaymentGateway;

/**
 * A registry of payment drivers keyed by their {@see PaymentGateway::code()}.
 * The twin of {@see ShippingManager}: Checkout registers the mock gateway now,
 * the Payments module (Part 11) registers Netopia/PayU into the same singleton.
 */
class PaymentManager
{
    /** @var array<string, PaymentGateway> */
    private array $drivers = [];

    public function register(PaymentGateway $driver): void
    {
        $this->drivers[$driver->code()] = $driver;
    }

    /**
     * @return PaymentGateway[]
     */
    public function available(): array
    {
        return array_values($this->drivers);
    }

    public function has(string $code): bool
    {
        return isset($this->drivers[$code]);
    }

    public function get(string $code): PaymentGateway
    {
        return $this->drivers[$code]
            ?? throw new InvalidArgumentException("Metodă de plată necunoscută: {$code}.");
    }
}
