<?php

declare(strict_types=1);

namespace Modules\Shipping\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShippingProvider;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

/**
 * Urgent Cargus courier (https://www.cargus.ro) via its Orders/ShipAndGo API.
 *
 * Same shape as {@see SamedayProvider}: the real calls (login for a token behind
 * an Ocp-Apim subscription key, `Shipments/GetTariff` for a quote, `Awbs` to
 * book) are all guarded by {@see self::isSandbox()}. Without a subscription key
 * and credentials the driver stays in SANDBOX — deterministic price, fake AWB,
 * zero network — so the tutorial runs end-to-end without a Cargus account.
 */
class CargusProvider implements ShippingProvider
{
    public function __construct(
        private readonly string $subscriptionKey = '',
        private readonly string $username = '',
        private readonly string $password = '',
        private readonly bool $sandbox = true,
        private readonly string $baseUrl = 'https://urgentcargus.azure-api.net/api',
        private readonly string $label = 'Cargus',
    ) {
    }

    public function code(): string
    {
        return 'cargus';
    }

    public function label(): string
    {
        return $this->label;
    }

    public function quote(ShippingContext $ctx): Money
    {
        if ($this->isSandbox()) {
            return $this->sandboxQuote($ctx);
        }

        $tariff = $this->client()
            ->withToken($this->authenticate())
            ->post($this->baseUrl.'/Shipments/GetTariff', [
                'FromLocalityId' => 0,
                'ToCounty' => $ctx->county,
                'ToLocality' => $ctx->city,
                'Parcels' => 1,
                'TotalWeight' => $ctx->weightKg,
                'ServiceId' => 34,
            ])
            ->throw()
            ->json('GrandTotal');

        return Money::fromMajor((float) $tariff);
    }

    public function createShipment(Shippable $order): string
    {
        if ($this->isSandbox()) {
            return $this->sandboxAwb($order);
        }

        $ctx = $order->shippingContext();

        return (string) $this->client()
            ->withToken($this->authenticate())
            ->post($this->baseUrl.'/Awbs', [
                'Recipient' => [
                    'CountyName' => $ctx->county,
                    'LocalityName' => $ctx->city,
                    'PostalCode' => $ctx->postalCode,
                ],
                'Parcels' => 1,
                'TotalWeight' => $ctx->weightKg,
                'ServiceId' => 34,
                'SenderReference1' => $order->shippableReference(),
            ])
            ->throw()
            ->json('BarCode');
    }

    /** Sandbox unless a subscription key AND credentials are all present. */
    public function isSandbox(): bool
    {
        return $this->sandbox
            || $this->subscriptionKey === ''
            || $this->username === ''
            || $this->password === '';
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders(['Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
            ->acceptJson();
    }

    private function authenticate(): string
    {
        return (string) $this->client()
            ->post($this->baseUrl.'/LoginUser', [
                'UserName' => $this->username,
                'Password' => $this->password,
            ])
            ->throw()
            ->json();
    }

    private function sandboxQuote(ShippingContext $ctx): Money
    {
        // Deterministic: a 22,90 lei base plus 4,50 lei per started kilogram.
        return Money::of(2290 + (int) ceil($ctx->weightKg) * 450);
    }

    private function sandboxAwb(Shippable $order): string
    {
        $ref = $order->shippableReference();

        return sprintf('SANDBOX-AWB-%s-%d', $ref, abs(crc32('cargus'.$ref)) % 100000);
    }
}
