<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Core\DataObjects\AddressData;
use Modules\Core\DataObjects\CartLine;
use Modules\Core\DataObjects\OrderDraft;
use Modules\Core\Events\OrderPlaced;
use Modules\Core\ValueObjects\Money;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Mail\OrderConfirmed;
use Modules\Orders\Models\Order;

uses(Tests\TestCase::class, RefreshDatabase::class);

/**
 * Build the same OrderDraft shape Checkout (Part 8) assembles: 2 x 75,00 lei
 * plus a 19,99 lei flat shipping = 169,99 lei total.
 */
function makeDraft(?int $userId = null): OrderDraft
{
    $address = new AddressData(
        name: 'Ion Popescu',
        phone: '0712345678',
        county: 'Cluj',
        city: 'Cluj-Napoca',
        street: 'Str. Memorandumului 1',
        postalCode: '400114',
    );

    return new OrderDraft(
        userId: $userId,
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
        paymentCode: 'mock',
        total: Money::of(16999),
    );
}

it('turns an OrderPlaced event into a persisted order with items and totals', function () {
    Mail::fake();

    OrderPlaced::dispatch(makeDraft());

    expect(Order::count())->toBe(1);

    $order = Order::with('items')->first();

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->number)->toBe('CMD-000001');
    expect($order->email)->toBe('ion@example.com');
    expect($order->customer_name)->toBe('Ion Popescu');
    expect($order->items_subtotal->getMinorAmount())->toBe(15000);
    expect($order->shipping_total->getMinorAmount())->toBe(1999);
    expect($order->total->getMinorAmount())->toBe(16999);
    expect($order->shipping_code)->toBe('flat');
    expect($order->payment_code)->toBe('mock');
    expect($order->shipping['city'])->toBe('Cluj-Napoca');
    expect($order->billing['county'])->toBe('Cluj');

    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->name)->toBe('Telefon Nova X1 — Negru');
    expect($order->items->first()->variant_id)->toBe(42);
    expect($order->items->first()->quantity)->toBe(2);
    expect($order->items->first()->unit_price->getMinorAmount())->toBe(7500);
    expect($order->items->first()->line_total->getMinorAmount())->toBe(15000);
});

it('links the order to a signed-in shopper via user_id', function () {
    Mail::fake();

    $user = User::factory()->create();

    OrderPlaced::dispatch(makeDraft($user->id));

    expect(Order::first()->user_id)->toBe($user->id);
});

it('pads the order number from the auto-increment id', function () {
    Mail::fake();

    OrderPlaced::dispatch(makeDraft());
    OrderPlaced::dispatch(makeDraft());

    expect(Order::pluck('number')->all())->toBe(['CMD-000001', 'CMD-000002']);
});

it('queues the OrderConfirmed mail to the order email', function () {
    Mail::fake();

    OrderPlaced::dispatch(makeDraft());

    Mail::assertQueued(OrderConfirmed::class, function (OrderConfirmed $mail) {
        return $mail->hasTo('ion@example.com');
    });
});

it('implements Payable and Shippable from Core', function () {
    Mail::fake();

    OrderPlaced::dispatch(makeDraft());
    $order = Order::first();

    expect($order)->toBeInstanceOf(\Modules\Core\Contracts\Payable::class);
    expect($order)->toBeInstanceOf(\Modules\Core\Contracts\Shippable::class);

    expect($order->payableReference())->toBe('CMD-000001');
    expect($order->payableAmount()->getMinorAmount())->toBe(16999);
    expect($order->shippableReference())->toBe('CMD-000001');

    $ctx = $order->shippingContext();
    expect($ctx->county)->toBe('Cluj');
    expect($ctx->city)->toBe('Cluj-Napoca');
    expect($ctx->postalCode)->toBe('400114');
    expect($ctx->cartSubtotal->getMinorAmount())->toBe(15000);
    expect($ctx->weightKg)->toBe(1.0); // 2 units x 0.5 kg
});
