<?php

declare(strict_types=1);

namespace Modules\Checkout\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Checkout\Services\PaymentManager;
use Modules\Checkout\Services\ShippingManager;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\DataObjects\AddressData;
use Modules\Core\DataObjects\CartData;
use Modules\Core\DataObjects\OrderDraft;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\Events\OrderPlaced;
use Modules\Customers\Models\Address;
use Modules\Customers\Models\Customer;

/**
 * The checkout wizard at `/finalizare-comanda` (route `storefront.checkout`).
 *
 * This is the linchpin of the whole store: it reads the cart through the
 * CartRepository contract, gathers an address (from a signed-in shopper's book
 * or a guest form), lets the shopper pick a shipping and a payment method from
 * the {@see ShippingManager}/{@see PaymentManager} registries, and — on "Plasează
 * comanda" — assembles a Core {@see OrderDraft} and fires the Core
 * {@see OrderPlaced} event. Checkout itself persists nothing: Orders, Shipping
 * and Payments are the listeners that turn the event into real work.
 *
 * State is kept entirely in the component (the `step` property drives the
 * wizard), so no `checkout_sessions` table is needed.
 */
#[Layout('core::layouts.storefront')]
#[Title('Finalizare comandă')]
class Checkout extends Component
{
    public const STEP_CART = 1;

    public const STEP_ADDRESS = 2;

    public const STEP_SHIPPING = 3;

    public const STEP_PAYMENT = 4;

    public const STEP_SUMMARY = 5;

    public int $step = self::STEP_CART;

    // Contact details carried onto the order.
    public string $email = '';

    public string $customerName = '';

    public string $phone = '';

    // Signed-in shopper: the picked address book entry, or a new-address form.
    public ?int $shippingAddressId = null;

    public bool $newAddress = false;

    /**
     * The shipping address form (guest, or a signed-in shopper adding a new one).
     * Billing equals shipping in Part 8; a separate billing address is a later
     * refinement.
     *
     * @var array<string, string>
     */
    public array $ship = [
        'name' => '',
        'phone' => '',
        'county' => '',
        'city' => '',
        'street' => '',
        'postal_code' => '',
    ];

    public ?string $shippingCode = null;

    public ?string $paymentCode = null;

    public function mount(): void
    {
        if ($this->cartData()->isEmpty()) {
            $this->redirect(route('storefront.cart'), navigate: true);

            return;
        }

        if (Auth::check()) {
            $user = Auth::user();
            $this->email = (string) $user->email;
            $this->customerName = (string) $user->name;

            $default = $this->addresses()->firstWhere('is_default', true) ?? $this->addresses()->first();

            if ($default !== null) {
                $this->shippingAddressId = $default->id;
                $this->phone = (string) $default->phone;
            } else {
                $this->newAddress = true;
            }
        }
    }

    // --- Address book (signed-in shopper) -----------------------------------

    /**
     * @return Collection<int, Address>
     */
    #[Computed]
    public function addresses(): Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        return Customer::firstOrCreate(['user_id' => Auth::id()])
            ->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
    }

    public function chooseAddress(int $addressId): void
    {
        $this->shippingAddressId = $addressId;
        $this->newAddress = false;
    }

    public function addNewAddress(): void
    {
        $this->shippingAddressId = null;
        $this->newAddress = true;
    }

    // --- Step navigation ----------------------------------------------------

    public function toAddress(): void
    {
        if ($this->cartData()->isEmpty()) {
            $this->redirect(route('storefront.cart'), navigate: true);

            return;
        }

        $this->step = self::STEP_ADDRESS;
    }

    public function toShipping(): void
    {
        $this->validateAddress();

        $this->step = self::STEP_SHIPPING;
        $this->shippingCode ??= app(ShippingManager::class)->available()[0]?->code();
    }

    public function toPayment(): void
    {
        $this->validate([
            'shippingCode' => ['required', Rule::in($this->shippingCodes())],
        ]);

        $this->step = self::STEP_PAYMENT;
        $this->paymentCode ??= app(PaymentManager::class)->available()[0]?->code();
    }

    public function toSummary(): void
    {
        $this->validate([
            'paymentCode' => ['required', Rule::in($this->paymentCodes())],
        ]);

        $this->step = self::STEP_SUMMARY;
    }

    public function back(): void
    {
        $this->step = max(self::STEP_CART, $this->step - 1);
    }

    // --- Placing the order --------------------------------------------------

    public function placeOrder(): void
    {
        $this->validate([
            'shippingCode' => ['required', Rule::in($this->shippingCodes())],
            'paymentCode' => ['required', Rule::in($this->paymentCodes())],
        ]);

        $cart = $this->cartData();

        if ($cart->isEmpty()) {
            $this->redirect(route('storefront.cart'), navigate: true);

            return;
        }

        $address = $this->resolveShippingAddress();
        $provider = app(ShippingManager::class)->get($this->shippingCode);
        $shippingCost = $provider->quote($this->buildShippingContext($cart, $address));

        // The correlation id for the whole order: minted here, before the order
        // exists, so we can both key the (idempotent) creation on it and redirect
        // the shopper to the confirmation page by a stable, public URL token.
        $reference = Str::uuid()->toString();

        $draft = new OrderDraft(
            reference: $reference,
            userId: Auth::id() !== null ? (int) Auth::id() : null,
            email: $this->email,
            customerName: $this->customerName,
            phone: $this->phone,
            billing: $address,
            shipping: $address,
            lines: $cart->lines,
            itemsSubtotal: $cart->subtotal,
            shippingCode: $provider->code(),
            shippingLabel: $provider->label(),
            shippingCost: $shippingCost,
            paymentCode: app(PaymentManager::class)->get($this->paymentCode)->code(),
            total: $cart->subtotal->plus($shippingCost),
        );

        OrderPlaced::dispatch($draft);

        $this->cart()->clear();

        // Hand off to the Orders-owned confirmation page by reference (a string
        // route name — Checkout never imports Order). From there the shopper can
        // pay online if the order needs it.
        $this->redirect(route('storefront.order.confirmation', $reference), navigate: true);
    }

    // --- View data ----------------------------------------------------------

    #[Computed]
    public function cartData(): CartData
    {
        return $this->cart()->get();
    }

    /**
     * The shipping options with a live quote for the chosen destination.
     *
     * @return array<int, array{code: string, label: string, cost: \Modules\Core\ValueObjects\Money}>
     */
    #[Computed]
    public function shippingOptions(): array
    {
        $context = $this->buildShippingContext($this->cartData(), $this->resolveShippingAddress());

        return array_map(fn ($provider): array => [
            'code' => $provider->code(),
            'label' => $provider->label(),
            'cost' => $provider->quote($context),
        ], app(ShippingManager::class)->available());
    }

    /**
     * @return array<int, array{code: string, label: string}>
     */
    #[Computed]
    public function paymentOptions(): array
    {
        return array_map(fn ($gateway): array => [
            'code' => $gateway->code(),
            'label' => $gateway->label(),
        ], app(PaymentManager::class)->available());
    }

    /**
     * Items subtotal, shipping cost and grand total for the summary step.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function totals(): array
    {
        $cart = $this->cartData();
        $provider = app(ShippingManager::class)->get($this->shippingCode);
        $shippingCost = $provider->quote($this->buildShippingContext($cart, $this->resolveShippingAddress()));

        return [
            'itemsSubtotal' => $cart->subtotal,
            'shippingLabel' => $provider->label(),
            'shippingCost' => $shippingCost,
            'total' => $cart->subtotal->plus($shippingCost),
        ];
    }

    public function render(): View
    {
        return view('checkout::livewire.checkout');
    }

    // --- Internals ----------------------------------------------------------

    private function validateAddress(): void
    {
        if (Auth::check() && ! $this->newAddress && $this->shippingAddressId !== null) {
            $this->validate([
                'shippingAddressId' => ['required', 'integer'],
                'phone' => ['required', 'string', 'max:30'],
            ]);

            abort_unless($this->addresses()->contains('id', $this->shippingAddressId), 403);

            return;
        }

        $this->validate([
            'email' => ['required', 'email'],
            'customerName' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'ship.name' => ['required', 'string', 'max:255'],
            'ship.phone' => ['required', 'string', 'max:30'],
            'ship.county' => ['required', 'string', 'max:100'],
            'ship.city' => ['required', 'string', 'max:100'],
            'ship.street' => ['required', 'string', 'max:255'],
            'ship.postal_code' => ['required', 'string', 'max:20'],
        ]);
    }

    private function resolveShippingAddress(): AddressData
    {
        if (Auth::check() && ! $this->newAddress && $this->shippingAddressId !== null) {
            $address = $this->addresses()->firstWhere('id', $this->shippingAddressId);

            abort_if($address === null, 403);

            return new AddressData(
                name: $address->name,
                phone: $address->phone,
                county: $address->county,
                city: $address->city,
                street: $address->street,
                postalCode: $address->postal_code,
            );
        }

        return new AddressData(
            name: $this->ship['name'],
            phone: $this->ship['phone'],
            county: $this->ship['county'],
            city: $this->ship['city'],
            street: $this->ship['street'],
            postalCode: $this->ship['postal_code'],
        );
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

    /**
     * @return string[]
     */
    private function shippingCodes(): array
    {
        return array_map(
            fn ($provider): string => $provider->code(),
            app(ShippingManager::class)->available(),
        );
    }

    /**
     * @return string[]
     */
    private function paymentCodes(): array
    {
        return array_map(
            fn ($gateway): string => $gateway->code(),
            app(PaymentManager::class)->available(),
        );
    }

    private function cart(): CartRepository
    {
        return app(CartRepository::class);
    }
}
