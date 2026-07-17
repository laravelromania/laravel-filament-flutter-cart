<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

/**
 * Loads a {@see Payable} by its stable correlation reference — the UUID stamped
 * on the {@see \Modules\Core\DataObjects\OrderDraft} at checkout time.
 *
 * Implemented by Orders (Part 9/11) and bound in the container. It exists so the
 * Payments module (Part 11) can resolve the order to charge — for both the
 * "initiate payment" entry point and the async gateway callback — WITHOUT ever
 * importing Orders' concrete Order model. Dependencies keep flowing toward Core:
 * Payments depends on this Core contract, Orders provides the implementation.
 */
interface OrderLocator
{
    public function byReference(string $reference): ?Payable;
}
