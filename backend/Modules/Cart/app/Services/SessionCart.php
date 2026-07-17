<?php

declare(strict_types=1);

namespace Modules\Cart\Services;

use Modules\Cart\Services\Concerns\BuildsCartData;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\DataObjects\CartData;

/**
 * A cart that lives in the session — the basket of an anonymous visitor. It
 * stores nothing but a `[variantId => qty]` map under the `cart` key; prices and
 * names are rebuilt from the catalog on every {@see self::get()} so a price
 * change is reflected immediately and stale money is never persisted.
 */
class SessionCart implements CartRepository
{
    use BuildsCartData;

    private const KEY = 'cart';

    public function get(): CartData
    {
        return $this->buildCartData($this->quantities());
    }

    public function add(string $variantId, int $qty): void
    {
        $cart = $this->quantities();
        $cart[$variantId] = ($cart[$variantId] ?? 0) + $qty;

        $this->store($cart);
    }

    public function update(string $variantId, int $qty): void
    {
        $cart = $this->quantities();

        if ($qty <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId] = $qty;
        }

        $this->store($cart);
    }

    public function remove(string $variantId): void
    {
        $cart = $this->quantities();
        unset($cart[$variantId]);

        $this->store($cart);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * @return array<int|string, int>
     */
    private function quantities(): array
    {
        return session()->get(self::KEY, []);
    }

    /**
     * @param  array<int|string, int>  $cart
     */
    private function store(array $cart): void
    {
        session()->put(self::KEY, $cart);
    }
}
