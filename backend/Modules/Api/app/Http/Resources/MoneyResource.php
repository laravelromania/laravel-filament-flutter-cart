<?php

declare(strict_types=1);

namespace Modules\Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\ValueObjects\Money;

/**
 * The one JSON shape every price in the API takes:
 *
 *   { "minor": 12990, "formatted": "129,90 lei", "currency": "RON" }
 *
 * `minor` is the integer amount in bani (never a float — money is never a float),
 * `formatted` is the ready-to-display Romanian string, `currency` is fixed at RON
 * for the series. Nested inside the other resources, so the storefront and the
 * Flutter app read prices identically. Being a JsonResource it never carries the
 * `data` wrapper when embedded — the wrapper only applies to a top-level response.
 *
 * @property-read Money $resource
 */
class MoneyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $money = $this->resource;

        return [
            'minor' => $money->getMinorAmount(),
            'formatted' => $money->format(),
            'currency' => $money->getCurrency(),
        ];
    }
}
