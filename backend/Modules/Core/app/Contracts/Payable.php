<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\ValueObjects\Money;

/**
 * Implemented by Orders\Order (Task 9). Lets a PaymentGateway charge "something
 * payable" without Core ever depending on the Orders module.
 */
interface Payable
{
    /** Human reference shown to the shopper / gateway, e.g. "CMD-000123". */
    public function payableReference(): string;

    public function payableAmount(): Money;
}
