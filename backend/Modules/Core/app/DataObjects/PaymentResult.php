<?php

declare(strict_types=1);

namespace Modules\Core\DataObjects;

/**
 * Normalized outcome of a gateway callback/IPN. Each PaymentGateway maps its
 * provider-specific payload into this shape so Orders (Task 9) never touches a
 * raw gateway response.
 */
readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $reference,
        public string $rawStatus,
    ) {
    }
}
