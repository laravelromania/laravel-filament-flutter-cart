<?php

declare(strict_types=1);

namespace Modules\Checkout\Services;

use Illuminate\Support\Str;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\DataObjects\AddressData;
use Modules\Core\DataObjects\CartData;
use Modules\Core\DataObjects\OrderDraft;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\Events\OrderPlaced;

/**
 * The single place where "a cart becomes an order" happens.
 *
 * Extracted in Part 12 so the two front-ends that place orders — the Part-8
 * Livewire checkout wizard and the Part-12 JSON API — share one implementation.
 * It mints the correlation {@see OrderDraft::$reference} (a UUID), quotes the
 * chosen courier through the {@see ShippingManager}, assembles the Core
 * {@see OrderDraft}, dispatches {@see OrderPlaced} (Orders/Shipping/Payments
 * listen) and clears the cart — then hands the draft back so the caller can
 * redirect (wizard) or load and serialise the created order (API).
 *
 * It persists nothing itself: exactly like the wizard did before, the OrderPlaced
 * listeners turn the draft into real work. Dependencies still flow toward Core —
 * this service speaks only in Core DTOs and the Checkout-owned manager registries.
 */
class PlaceOrder
{
    public function __construct(
        private readonly ShippingManager $shipping,
        private readonly PaymentManager $payments,
    ) {
    }

    /**
     * @param  string  $shippingCode  a registered {@see \Modules\Core\Contracts\ShippingProvider::code()}
     * @param  string  $paymentCode   a registered {@see \Modules\Core\Contracts\PaymentGateway::code()}
     * @param  int|null $userId        the buyer's id, or null for a guest checkout
     */
    public function __invoke(
        CartData $cart,
        AddressData $billing,
        AddressData $shipping,
        string $email,
        string $customerName,
        string $phone,
        string $shippingCode,
        string $paymentCode,
        ?int $userId,
    ): OrderDraft {
        $provider = $this->shipping->get($shippingCode);
        $gateway = $this->payments->get($paymentCode);

        $shippingCost = $provider->quote($this->buildShippingContext($cart, $shipping));

        // The correlation id for the whole order: minted here, before the order
        // exists, so Orders can key its idempotent firstOrCreate() on it and the
        // storefront can address the order by a stable, unguessable URL token.
        $reference = Str::uuid()->toString();

        $draft = new OrderDraft(
            reference: $reference,
            userId: $userId,
            email: $email,
            customerName: $customerName,
            phone: $phone,
            billing: $billing,
            shipping: $shipping,
            lines: $cart->lines,
            itemsSubtotal: $cart->subtotal,
            shippingCode: $provider->code(),
            shippingLabel: $provider->label(),
            shippingCost: $shippingCost,
            paymentCode: $gateway->code(),
            total: $cart->subtotal->plus($shippingCost),
        );

        OrderPlaced::dispatch($draft);

        // Resolve the cart lazily (not via constructor) so the binding is read at
        // invocation time — a request that authenticated mid-flight clears the
        // right basket (DatabaseCart for a token'd API call, SessionCart for a guest).
        app(CartRepository::class)->clear();

        return $draft;
    }

    private function buildShippingContext(CartData $cart, AddressData $address): ShippingContext
    {
        return new ShippingContext(
            county: $address->county,
            city: $address->city,
            postalCode: $address->postalCode,
            weightKg: max(0.5, $cart->itemCount * 0.5),
            cartSubtotal: $cart->subtotal,
        );
    }
}
