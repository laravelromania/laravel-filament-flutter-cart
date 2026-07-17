<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\DataObjects\CartData;

/**
 * The cart, abstracted away from where it lives. Task 6 provides a session-backed
 * and a database-backed implementation and binds one in the container; the rest
 * of the app only ever depends on this interface.
 */
interface CartRepository
{
    public function get(): CartData;

    public function add(string $variantId, int $qty): void;

    public function update(string $variantId, int $qty): void;

    public function remove(string $variantId): void;

    public function clear(): void;
}
