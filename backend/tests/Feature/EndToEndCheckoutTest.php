<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Modules\Cart\Livewire\CartDrawer;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Checkout\Livewire\Checkout;
use Modules\Checkout\Services\PaymentManager;
use Modules\Core\Contracts\CartRepository;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

uses(RefreshDatabase::class);

/**
 * The one true "click through the whole storefront" test: a shopper adds a
 * real product to the cart through the Cart module's own drawer component
 * (not a direct CartRepository::add() call), walks the Checkout wizard with a
 * REAL online gateway (not the 'mock' placeholder every other Checkout test
 * uses), which — through the real OrderPlaced event, not a faked one — leaves
 * a genuine row in `orders` care of the Orders module's listener. It then
 * plays the Netopia SANDBOX callback (the same signed-POST contract a real
 * gateway IPN would use) at Payments' own route, and asserts the order comes
 * out the other side paid. Catalog, Cart, Checkout, Orders and Payments all
 * have to cooperate correctly for this single test to pass — no module test
 * alone exercises the full chain end to end.
 */
it('adds a product to the cart, checks out with a real gateway, and pays via the sandbox callback', function () {
    Mail::fake();

    $product = Product::factory()->create(['name' => 'Telefon E2E', 'price' => 75_00]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => null, 'stock' => 10]);

    // "Add" — driven through the Cart module's own drawer component, exactly
    // as the browser would dispatch it from the product page.
    Livewire::test(CartDrawer::class)->call('addToCart', $variant->id, 2);

    expect(app(CartRepository::class)->get()->isEmpty())->toBeFalse();

    // "Checkout" — the real wizard, with a real gateway code so Payments is
    // genuinely exercised (every other Checkout test uses 'mock').
    Livewire::test(Checkout::class)
        ->call('toAddress')
        ->set('email', 'ion@example.com')
        ->set('customerName', 'Ion Popescu')
        ->set('phone', '0712345678')
        ->set('ship.name', 'Ion Popescu')
        ->set('ship.phone', '0712345678')
        ->set('ship.county', 'Cluj')
        ->set('ship.city', 'Cluj-Napoca')
        ->set('ship.street', 'Str. Memorandumului 1')
        ->set('ship.postal_code', '400114')
        ->call('toShipping')
        ->set('shippingCode', 'flat')
        ->call('toPayment')
        ->set('paymentCode', 'netopia')
        ->call('toSummary')
        ->call('placeOrder');

    // The cart was cleared by the real PlaceOrder service.
    expect(app(CartRepository::class)->get()->isEmpty())->toBeTrue();

    // "OrderPlaced" — a genuine order row, not a mocked/faked event.
    expect(Order::count())->toBe(1);

    $order = Order::first();

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->paid_at)->toBeNull();
    expect($order->payment_code)->toBe('netopia');
    expect($order->items_subtotal->getMinorAmount())->toBe(15000); // 2 x 75,00 lei

    // "sandbox-callback" — the signed POST a real Netopia IPN would send.
    $driver = app(PaymentManager::class)->get('netopia');
    $signature = $driver->sandboxSignature($order->number, 'confirmed');

    $this->post(route('payments.callback', ['gateway' => 'netopia']), [
        'reference' => $order->number,
        'status' => 'confirmed',
        'signature' => $signature,
    ])->assertOk();

    // "paid"
    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    expect($order->fresh()->paid_at)->not->toBeNull();
});
