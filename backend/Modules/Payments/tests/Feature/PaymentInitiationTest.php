<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Checkout\Services\PaymentManager;
use Modules\Core\Contracts\OrderLocator;
use Modules\Core\DataObjects\AddressData;
use Modules\Core\DataObjects\CartLine;
use Modules\Core\DataObjects\OrderDraft;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Core\Events\OrderPlaced;
use Modules\Core\ValueObjects\Money;

uses(Tests\TestCase::class, RefreshDatabase::class);

/**
 * Places a real order via the Core OrderPlaced event (the Orders module's own
 * CreateOrderFromCheckout listener persists it) and returns its UUID
 * `reference`. Payments must never import the Orders module, not even in tests.
 */
function placeOrderFor(string $paymentCode): string
{
    Mail::fake();

    $reference = (string) Str::uuid();

    $address = new AddressData(
        name: 'Ion Popescu',
        phone: '0712345678',
        county: 'Cluj',
        city: 'Cluj-Napoca',
        street: 'Str. Memorandumului 1',
        postalCode: '400114',
    );

    OrderPlaced::dispatch(new OrderDraft(
        reference: $reference,
        userId: null,
        email: 'ion@example.com',
        customerName: 'Ion Popescu',
        phone: '0712345678',
        billing: $address,
        shipping: $address,
        lines: [
            new CartLine(
                variantId: '42',
                name: 'Telefon Nova X1 — Negru',
                unitPrice: Money::of(7500),
                quantity: 2,
                lineTotal: Money::of(15000),
            ),
        ],
        itemsSubtotal: Money::of(15000),
        shippingCode: 'flat',
        shippingLabel: 'Livrare standard prin curier',
        shippingCost: Money::of(1999),
        paymentCode: $paymentCode,
        total: Money::of(16999),
    ));

    return $reference;
}

it('returns a PaymentRedirect to the internal simulator in sandbox, with no external call', function () {
    Http::fake(); // any real HTTP would be recorded

    $reference = placeOrderFor('netopia');
    $order = app(OrderLocator::class)->byReference($reference);

    $redirect = app(PaymentManager::class)->get('netopia')->initiate($order, $reference);

    expect($redirect)->toBeInstanceOf(PaymentRedirect::class);
    expect($redirect->method)->toBe('GET');
    expect($redirect->url)->toContain('/plati/netopia/simuleaza/');
    expect($redirect->url)->toContain($reference);

    // The sandbox URL must be keyed on the unguessable UUID, never the
    // sequential order number — that is the whole point of this fix.
    expect($redirect->url)->not->toContain($order->payableReference());

    // The whole point of sandbox: not one byte leaves for a real gateway.
    Http::assertNothingSent();
});

it('routes the initiate entry point through the OrderLocator to the gateway redirect', function () {
    Http::fake();

    $reference = placeOrderFor('payu');

    $this->get(route('payments.initiate', ['reference' => $reference]))
        ->assertRedirect(route('payments.simulate', [
            'gateway' => 'payu',
            'reference' => $reference,
        ]));

    Http::assertNothingSent();
});

it('404s the initiate entry point for an unknown reference', function () {
    $this->get(route('payments.initiate', ['reference' => 'does-not-exist']))
        ->assertNotFound();
});

it('404s the simulate entry point for an unknown reference', function () {
    $this->get(route('payments.simulate', [
        'gateway' => 'netopia',
        'reference' => (string) Str::uuid(),
    ]))->assertNotFound();
});

it('404s the simulate entry point once the order is already paid', function () {
    $reference = placeOrderFor('netopia');

    DB::table('orders')->where('reference', $reference)->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->get(route('payments.simulate', ['gateway' => 'netopia', 'reference' => $reference]))
        ->assertNotFound();
});
