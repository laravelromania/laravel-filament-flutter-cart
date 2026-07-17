<?php

declare(strict_types=1);

namespace Modules\Cart\Listeners;

use Illuminate\Auth\Events\Login;
use Modules\Cart\Services\DatabaseCart;
use Modules\Cart\Services\SessionCart;

/**
 * When a visitor logs in, fold whatever they had gathered as a guest into their
 * persistent cart, then empty the session basket. Registered on
 * {@see \Illuminate\Auth\Events\Login} in the Cart EventServiceProvider.
 *
 * We target the user carried by the event (not `Auth::id()`): the session guard
 * fires `Login` before it finishes setting the current user, so reading the
 * event is the only reliable way to know who just signed in.
 */
class MergeGuestCart
{
    public function __construct(private readonly SessionCart $sessionCart)
    {
    }

    public function handle(Login $event): void
    {
        $guest = $this->sessionCart->get();

        if ($guest->isEmpty()) {
            return;
        }

        $userCart = new DatabaseCart((int) $event->user->getAuthIdentifier());

        foreach ($guest->lines as $line) {
            $userCart->add($line->variantId, $line->quantity);
        }

        $this->sessionCart->clear();
    }
}
