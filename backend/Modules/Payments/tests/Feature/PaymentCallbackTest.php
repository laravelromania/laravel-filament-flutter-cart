<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Checkout\Services\PaymentManager;
use Modules\Core\Contracts\OrderLocator;
use Modules\Core\DataObjects\AddressData;
use Modules\Core\DataObjects\CartLine;
use Modules\Core\DataObjects\OrderDraft;
use Modules\Core\Events\OrderPlaced;
use Modules\Core\Events\PaymentCompleted;
use Modules\Core\ValueObjects\Money;

uses(Tests\TestCase::class, RefreshDatabase::class);

/**
 * Places a real order by dispatching the same Core OrderPlaced event Checkout
 * fires at "Plasează comanda" — the Orders module's own CreateOrderFromCheckout
 * listener persists it. Payments must never import the Orders module, not even
 * in tests (that is the whole point of the Core OrderLocator contract), so this
 * — not an Order factory — is how this module builds an order to exercise.
 * Mirrors how Orders' own checkout-listener test builds its draft.
 *
 * Returns the order's UUID `reference` — the token Payments' own routes use.
 */
function placeOrder(string $paymentCode = 'netopia'): string
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

/**
 * The order NUMBER (e.g. "CMD-000001") behind a placed order's UUID reference —
 * loaded through the Core OrderLocator/Payable contracts, never Order:: directly.
 */
function orderNumberFor(string $reference): string
{
    return app(OrderLocator::class)->byReference($reference)->payableReference();
}

/** The correctly-signed sandbox callback payload for a placed order. */
function netopiaCallback(string $reference, string $status = 'confirmed'): array
{
    $driver = app(PaymentManager::class)->get('netopia');
    $orderNumber = orderNumberFor($reference);

    return [
        'reference' => $orderNumber,
        'status' => $status,
        'signature' => $driver->sandboxSignature($orderNumber, $status),
    ];
}

it('marks the order paid on a signature-verified successful callback', function () {
    Http::fake();

    $reference = placeOrder('netopia');

    $this->post(route('payments.callback', ['gateway' => 'netopia']), netopiaCallback($reference))
        ->assertOk();

    $this->assertDatabaseHas('orders', ['reference' => $reference, 'status' => 'paid']);

    $row = DB::table('orders')->where('reference', $reference)->first();
    expect($row->paid_at)->not->toBeNull();

    Http::assertNothingSent();
});

it('rejects a callback with an invalid signature and leaves the order pending', function () {
    $reference = placeOrder('netopia');

    $forged = netopiaCallback($reference);
    $forged['signature'] = 'forged-signature';

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $forged)
        ->assertForbidden();

    $this->assertDatabaseHas('orders', ['reference' => $reference, 'status' => 'pending']);

    $row = DB::table('orders')->where('reference', $reference)->first();
    expect($row->paid_at)->toBeNull();
});

it('does not dispatch PaymentCompleted for a forged callback', function () {
    Event::fake([PaymentCompleted::class]);

    $reference = placeOrder('netopia');

    $forged = netopiaCallback($reference);
    $forged['signature'] = 'forged-signature';

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $forged)
        ->assertForbidden();

    Event::assertNotDispatched(PaymentCompleted::class);
});

it('leaves the order pending on a verified but unsuccessful (canceled) callback', function () {
    $reference = placeOrder('payu');

    $driver = app(PaymentManager::class)->get('payu');
    $orderNumber = orderNumberFor($reference);

    $this->post(route('payments.callback', ['gateway' => 'payu']), [
        'reference' => $orderNumber,
        'status' => 'canceled',
        'signature' => $driver->sandboxSignature($orderNumber, 'canceled'),
    ])->assertOk();

    $this->assertDatabaseHas('orders', ['reference' => $reference, 'status' => 'pending']);

    $row = DB::table('orders')->where('reference', $reference)->first();
    expect($row->paid_at)->toBeNull();
});

it('is idempotent — a replayed callback keeps a single paid_at', function () {
    $reference = placeOrder('netopia');

    $payload = netopiaCallback($reference);

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $payload)->assertOk();
    $firstPaidAt = DB::table('orders')->where('reference', $reference)->value('paid_at');

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $payload)->assertOk();
    $secondPaidAt = DB::table('orders')->where('reference', $reference)->value('paid_at');

    expect($secondPaidAt)->toBe($firstPaidAt);
    $this->assertDatabaseHas('orders', ['reference' => $reference, 'status' => 'paid']);
});
