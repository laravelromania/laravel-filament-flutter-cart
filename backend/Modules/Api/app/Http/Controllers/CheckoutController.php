<?php

declare(strict_types=1);

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Api\Http\Resources\MoneyResource;
use Modules\Api\Http\Resources\OrderResource;
use Modules\Checkout\Services\PaymentManager;
use Modules\Checkout\Services\PlaceOrder;
use Modules\Checkout\Services\ShippingManager;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\DataObjects\AddressData;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Orders\Models\Order;

/**
 * Checkout over the API. Two endpoints:
 *
 *  - `GET  /checkout/shipping-methods` — the couriers registered in the
 *    {@see ShippingManager}, each with a live quote for the caller's cart and
 *    (optional) destination, so the app can show delivery options and prices.
 *  - `POST /checkout` — places the order through the SAME {@see PlaceOrder}
 *    service the Livewire wizard uses (single source of truth), then loads and
 *    serialises the created order. When the chosen gateway needs an online
 *    payment, a `payment` redirect payload is attached for the app to follow.
 */
class CheckoutController
{
    public function shippingMethods(Request $request, CartRepository $cart, ShippingManager $shipping): JsonResponse
    {
        $data = $cart->get();

        $context = new ShippingContext(
            county: (string) $request->query('county', ''),
            city: (string) $request->query('city', ''),
            postalCode: (string) $request->query('postal_code', ''),
            weightKg: max(0.5, $data->itemCount * 0.5),
            cartSubtotal: $data->subtotal,
        );

        $methods = array_map(fn ($provider): array => [
            'code' => $provider->code(),
            'label' => $provider->label(),
            'cost' => MoneyResource::make($provider->quote($context)),
        ], $shipping->available());

        return response()->json(['data' => $methods]);
    }

    public function store(
        Request $request,
        CartRepository $cart,
        ShippingManager $shipping,
        PaymentManager $payments,
        PlaceOrder $placeOrder,
    ): JsonResponse {
        $data = $request->validate([
            'billing' => ['required', 'array'],
            'shipping' => ['required', 'array'],
            'shippingCode' => ['required', 'string', Rule::in($this->codes($shipping->available()))],
            'paymentCode' => ['required', 'string', Rule::in($this->codes($payments->available()))],
            ...$this->addressRules('billing'),
            ...$this->addressRules('shipping'),
        ]);

        $cartData = $cart->get();

        if ($cartData->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Coșul este gol.'],
            ]);
        }

        $billing = $this->address($data['billing']);
        $shippingAddress = $this->address($data['shipping']);
        $user = $request->user();

        $draft = $placeOrder(
            cart: $cartData,
            billing: $billing,
            shipping: $shippingAddress,
            email: (string) $user->email,
            customerName: (string) $user->name,
            phone: $shippingAddress->phone,
            shippingCode: $data['shippingCode'],
            paymentCode: $data['paymentCode'],
            userId: (int) Auth::id(),
        );

        // The API MAY depend on Orders (it is the composition layer): load the
        // order the OrderPlaced listener just created, keyed on the draft's UUID.
        $order = Order::with('items')->where('reference', $draft->reference)->firstOrFail();

        return (new OrderResource($order))
            ->additional(['payment' => $this->paymentPayload($order, $payments)])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * When the order was placed with a real online gateway (not the Part-8 'mock'
     * placeholder), initiate the payment and return the redirect the app should
     * follow (a hosted page URL). Cash-on-delivery / mock orders return null.
     *
     * @return array<string, mixed>|null
     */
    private function paymentPayload(Order $order, PaymentManager $payments): ?array
    {
        if ($order->payment_code === 'mock' || ! $payments->has($order->payment_code)) {
            return null;
        }

        $redirect = $payments->get($order->payment_code)->initiate($order, $order->reference);

        return [
            'url' => $redirect->url,
            'method' => $redirect->method,
            'fields' => $redirect->fields,
        ];
    }

    private function address(array $input): AddressData
    {
        return new AddressData(
            name: (string) $input['name'],
            phone: (string) $input['phone'],
            county: (string) $input['county'],
            city: (string) $input['city'],
            street: (string) $input['street'],
            postalCode: (string) $input['postal_code'],
        );
    }

    /**
     * @param  array<int, object{code: callable-string}>  $drivers
     * @return string[]
     */
    private function codes(array $drivers): array
    {
        return array_map(fn ($driver): string => $driver->code(), $drivers);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function addressRules(string $key): array
    {
        return [
            "{$key}.name" => ['required', 'string', 'max:255'],
            "{$key}.phone" => ['required', 'string', 'max:30'],
            "{$key}.county" => ['required', 'string', 'max:100'],
            "{$key}.city" => ['required', 'string', 'max:100'],
            "{$key}.street" => ['required', 'string', 'max:255'],
            "{$key}.postal_code" => ['required', 'string', 'max:20'],
        ];
    }
}
