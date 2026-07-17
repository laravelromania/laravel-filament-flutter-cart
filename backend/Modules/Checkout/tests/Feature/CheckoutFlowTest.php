<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Checkout\Livewire\Checkout;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\Events\OrderPlaced;
use Modules\Customers\Models\Address;
use Modules\Customers\Models\Customer;

uses(Tests\TestCase::class, RefreshDatabase::class);

/**
 * Seed one variant priced at 75,00 lei and drop `$qty` of it into whatever cart
 * the current auth state resolves.
 */
function seedCartVariant(int $qty = 2): ProductVariant
{
    $product = Product::factory()->create(['name' => 'Produs Checkout', 'price' => 7500]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => null, 'stock' => 10]);

    app(CartRepository::class)->add((string) $variant->id, $qty);

    return $variant;
}

it('serves /finalizare-comanda with the Checkout component', function () {
    seedCartVariant();

    $this->get('/finalizare-comanda')
        ->assertOk()
        ->assertSeeLivewire(Checkout::class);
});

it('redirects an empty cart back to /cos', function () {
    Livewire::test(Checkout::class)->assertRedirect('/cos');
});

it('walks a guest through the steps and dispatches OrderPlaced with the right totals', function () {
    Event::fake([OrderPlaced::class]);

    seedCartVariant(2); // 2 x 75,00 = 150,00 lei

    $component = Livewire::test(Checkout::class)
        ->assertSet('step', 1)
        ->call('toAddress')
        ->assertSet('step', 2)
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
        ->assertSet('step', 3)
        ->set('shippingCode', 'flat')
        ->call('toPayment')
        ->assertSet('step', 4)
        ->set('paymentCode', 'mock')
        ->call('toSummary')
        ->assertSet('step', 5)
        ->call('placeOrder');

    $reference = null;

    Event::assertDispatched(OrderPlaced::class, function (OrderPlaced $event) use (&$reference) {
        $draft = $event->draft;
        $reference = $draft->reference;

        return $draft->userId === null
            && $draft->email === 'ion@example.com'
            && $draft->customerName === 'Ion Popescu'
            && $draft->itemsSubtotal->getMinorAmount() === 15000
            && $draft->shippingCode === 'flat'
            && $draft->shippingCost->getMinorAmount() === 1999
            && $draft->paymentCode === 'mock'
            && $draft->total->getMinorAmount() === 16999
            && count($draft->lines) === 1
            && $draft->shipping->city === 'Cluj-Napoca'
            && $draft->billing->city === 'Cluj-Napoca';
    });

    expect($reference)->not->toBeNull();
    $component->assertRedirect(route('storefront.order.confirmation', $reference));
});

it('clears the cart after the order is placed', function () {
    seedCartVariant(2);

    expect(app(CartRepository::class)->get()->isEmpty())->toBeFalse();

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
        ->set('paymentCode', 'mock')
        ->call('toSummary')
        ->call('placeOrder');

    expect(app(CartRepository::class)->get()->isEmpty())->toBeTrue();
});

it('will not advance past the address step with an incomplete form', function () {
    seedCartVariant();

    Livewire::test(Checkout::class)
        ->call('toAddress')
        ->call('toShipping')
        ->assertHasErrors(['email', 'ship.county'])
        ->assertSet('step', 2);
});

it('lets an authenticated shopper pick an address from the book and place the order', function () {
    Event::fake([OrderPlaced::class]);

    $user = User::factory()->create(['name' => 'Maria Ionescu', 'email' => 'maria@example.com']);
    $this->actingAs($user);

    $customer = Customer::factory()->create(['user_id' => $user->id, 'phone' => '0733222111']);
    $address = Address::factory()->for($customer)->create([
        'type' => 'shipping',
        'city' => 'Iași',
        'county' => 'Iași',
    ]);

    seedCartVariant(1); // 1 x 75,00 = 75,00

    $component = Livewire::test(Checkout::class)
        ->call('toAddress')
        ->set('shippingAddressId', $address->id)
        ->call('toShipping')
        ->set('shippingCode', 'flat')
        ->call('toPayment')
        ->set('paymentCode', 'mock')
        ->call('toSummary')
        ->call('placeOrder');

    $reference = null;

    Event::assertDispatched(OrderPlaced::class, function (OrderPlaced $event) use ($user, $address, &$reference) {
        $draft = $event->draft;
        $reference = $draft->reference;

        return $draft->userId === $user->id
            && $draft->shipping->city === $address->city
            && $draft->itemsSubtotal->getMinorAmount() === 7500
            && $draft->total->getMinorAmount() === 9499; // 7500 + 1999
    });

    $component->assertRedirect(route('storefront.order.confirmation', $reference));
});
