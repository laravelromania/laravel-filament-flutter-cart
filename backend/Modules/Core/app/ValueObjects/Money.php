<?php

declare(strict_types=1);

namespace Modules\Core\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable monetary value stored as integer minor units (bani).
 *
 * The whole series uses a single currency (RON); a `$currency` field is kept so
 * the guards read naturally and a future multi-currency store has a seam to grow
 * into. Never build a Money from a float column — store minor units and cast.
 */
final class Money
{
    private function __construct(
        private readonly int $minor,
        private readonly string $currency,
    ) {
    }

    /** Build from minor units (bani): of(12990) === 129,90 lei. */
    public static function of(int $minor, string $currency = 'RON'): self
    {
        return new self($minor, strtoupper($currency));
    }

    /** Build from a major amount: fromMajor(129.90) === 12990 bani. */
    public static function fromMajor(string|float $major, string $currency = 'RON'): self
    {
        return new self((int) round(((float) $major) * 100), strtoupper($currency));
    }

    public function getMinorAmount(): int
    {
        return $this->minor;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function plus(Money $other): Money
    {
        $this->assertSameCurrency($other);

        return new self($this->minor + $other->minor, $this->currency);
    }

    public function minus(Money $other): Money
    {
        $this->assertSameCurrency($other);

        return new self($this->minor - $other->minor, $this->currency);
    }

    public function times(int $factor): Money
    {
        return new self($this->minor * $factor, $this->currency);
    }

    public function isZero(): bool
    {
        return $this->minor === 0;
    }

    public function equals(Money $other): bool
    {
        return $this->minor === $other->minor && $this->currency === $other->currency;
    }

    /** Romanian formatting: 12990 -> "129,90 lei" (comma decimal, space thousands). */
    public function format(): string
    {
        return number_format($this->minor / 100, 2, ',', ' ').' lei';
    }

    public function __toString(): string
    {
        return $this->format();
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot combine Money of {$this->currency} with {$other->currency}."
            );
        }
    }
}
