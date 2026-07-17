<?php

declare(strict_types=1);

namespace Modules\Cart\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Cart\Models\Cart;
use Modules\Cart\Services\Concerns\BuildsCartData;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\DataObjects\CartData;

/**
 * A cart persisted in the database — the basket of a logged-in customer, kept
 * across devices and sessions. Rows live in `carts` / `cart_items`; like the
 * session cart it stores only variant ids and quantities and rebuilds prices
 * from the catalog on read.
 *
 * The target user defaults to the authenticated one (resolved lazily, per call,
 * so the container binding never captures a stale identity), but a specific id
 * can be passed — that is how the login-merge listener writes into the freshly
 * authenticated user's cart before the guard has finished setting the user.
 */
class DatabaseCart implements CartRepository
{
    use BuildsCartData;

    public function __construct(private readonly ?int $userId = null)
    {
    }

    public function get(): CartData
    {
        /** @var array<int, int> $quantities */
        $quantities = $this->cart()->items()->pluck('qty', 'variant_id')->all();

        return $this->buildCartData($quantities);
    }

    public function add(string $variantId, int $qty): void
    {
        $item = $this->cart()->items()->firstOrNew(['variant_id' => $variantId]);
        $item->qty = ($item->qty ?? 0) + $qty;
        $item->save();
    }

    public function update(string $variantId, int $qty): void
    {
        if ($qty <= 0) {
            $this->remove($variantId);

            return;
        }

        $this->cart()->items()->updateOrCreate(
            ['variant_id' => $variantId],
            ['qty' => $qty],
        );
    }

    public function remove(string $variantId): void
    {
        $this->cart()->items()->where('variant_id', $variantId)->delete();
    }

    public function clear(): void
    {
        $this->cart()->items()->delete();
    }

    /**
     * The (single) cart row for the target user, created on first use.
     */
    private function cart(): Cart
    {
        return Cart::firstOrCreate(['user_id' => $this->userId ?? Auth::id()]);
    }
}
