<?php

declare(strict_types=1);

namespace Modules\Core\DataObjects;

/**
 * Instruction for the storefront to hand the shopper over to a payment gateway.
 * A GET redirect uses only $url; a form-POST gateway (e.g. Netopia) fills
 * $method='POST' and $fields with the hidden inputs to submit.
 */
readonly class PaymentRedirect
{
    /**
     * @param  array<string, string>  $fields
     */
    public function __construct(
        public string $url,
        public string $method = 'GET',
        public array $fields = [],
    ) {
    }
}
