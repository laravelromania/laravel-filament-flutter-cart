<?php

declare(strict_types=1);

namespace Modules\Shipping\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShippingProvider;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

/**
 * Sameday courier (https://sameday.ro) via its eAWB API.
 *
 * The real integration is here — authenticate for a token, ask `estimate-cost`
 * for a live tariff, POST `awb` to book a shipment — but every network call is
 * gated behind {@see self::isSandbox()}. With no credentials in `.env` (the
 * default for the tutorial) the driver runs in SANDBOX: `quote()` returns a
 * deterministic price computed from the parcel weight and `createShipment()`
 * returns a fake `SANDBOX-AWB-…` number, both WITHOUT touching the network.
 *
 * To go live you need a real Sameday account; set SHIPPING_SAMEDAY_USERNAME /
 * _PASSWORD and SHIPPING_SAMEDAY_SANDBOX=false and the same code hits the API.
 */
class SamedayProvider implements ShippingProvider
{
    public function __construct(
        private readonly string $username = '',
        private readonly string $password = '',
        private readonly bool $sandbox = true,
        private readonly string $baseUrl = 'https://sameday-api.demo.zitec.com',
        private readonly string $label = 'Sameday',
    ) {
    }

    public function code(): string
    {
        return 'sameday';
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

        $cost = Http::withToken($this->authenticate())
            ->acceptJson()
            ->asForm()
            ->post($this->baseUrl.'/api/awb/estimate-cost', [
                'pickupPoint' => 1,
                'packageType' => 0,
                'packageWeight' => $ctx->weightKg,
                'service' => 7,
                'awbPayment' => 1,
                'cashOnDelivery' => 0,
                'awbRecipient' => [
                    'county' => $ctx->county,
                    'city' => $ctx->city,
                    'postalCode' => $ctx->postalCode,
                ],
            ])
            ->throw()
            ->json('cost');

        return Money::fromMajor((float) $cost);
    }

    public function createShipment(Shippable $order): string
    {
        if ($this->isSandbox()) {
            return $this->sandboxAwb($order);
        }

        $ctx = $order->shippingContext();

        return (string) Http::withToken($this->authenticate())
            ->acceptJson()
            ->asForm()
            ->post($this->baseUrl.'/api/awb', [
                'pickupPoint' => 1,
                'packageType' => 0,
                'packageWeight' => $ctx->weightKg,
                'service' => 7,
                'awbPayment' => 1,
                'awbRecipient' => [
                    'county' => $ctx->county,
                    'city' => $ctx->city,
                    'postalCode' => $ctx->postalCode,
                ],
                'clientInternalReference' => $order->shippableReference(),
            ])
            ->throw()
            ->json('awbNumber');
    }

    /**
     * Sandbox is on when explicitly configured OR when credentials are missing —
     * so the store can never accidentally fire a real request without an account.
     */
    public function isSandbox(): bool
    {
        return $this->sandbox || $this->username === '' || $this->password === '';
    }

    private function authenticate(): string
    {
        return (string) Http::withHeaders([
            'X-Auth-Username' => $this->username,
            'X-Auth-Password' => $this->password,
        ])
            ->acceptJson()
            ->post($this->baseUrl.'/api/authenticate', ['remember_me' => true])
            ->throw()
            ->json('token');
    }

    private function sandboxQuote(ShippingContext $ctx): Money
    {
        // Deterministic: a 19,90 lei base plus 5 lei per started kilogram.
        return Money::of(1990 + (int) ceil($ctx->weightKg) * 500);
    }

    private function sandboxAwb(Shippable $order): string
    {
        $ref = $order->shippableReference();

        return sprintf('SANDBOX-AWB-%s-%d', $ref, abs(crc32('sameday'.$ref)) % 100000);
    }
}
