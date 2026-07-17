<?php

use Modules\Core\ValueObjects\Money;

it('creates money from minor units with default RON currency', function () {
    $money = Money::of(12990);

    expect($money->getMinorAmount())->toBe(12990)
        ->and($money->getCurrency())->toBe('RON');
});

it('creates money from a major amount', function () {
    expect(Money::fromMajor(129.90)->getMinorAmount())->toBe(12990)
        ->and(Money::fromMajor('129.90')->getMinorAmount())->toBe(12990)
        ->and(Money::fromMajor(0)->getMinorAmount())->toBe(0);
});

it('adds two amounts of the same currency', function () {
    $sum = Money::of(12990)->plus(Money::of(1010));

    expect($sum->getMinorAmount())->toBe(14000)
        ->and($sum)->not->toBe(Money::of(12990)); // immutable: new instance
});

it('subtracts two amounts of the same currency', function () {
    expect(Money::of(14000)->minus(Money::of(1010))->getMinorAmount())->toBe(12990);
});

it('multiplies by an integer factor', function () {
    expect(Money::of(12990)->times(3)->getMinorAmount())->toBe(38970);
});

it('knows when it is zero and compares by value', function () {
    expect(Money::of(0)->isZero())->toBeTrue()
        ->and(Money::of(100)->isZero())->toBeFalse()
        ->and(Money::of(100)->equals(Money::of(100)))->toBeTrue()
        ->and(Money::of(100)->equals(Money::of(200)))->toBeFalse();
});

it('formats amounts the Romanian way', function () {
    expect(Money::of(12990)->format())->toBe('129,90 lei')
        ->and(Money::of(5)->format())->toBe('0,05 lei')
        ->and(Money::of(100000)->format())->toBe('1 000,00 lei');
});

it('guards against mixing currencies', function () {
    expect(fn () => Money::of(100, 'RON')->plus(Money::of(100, 'EUR')))
        ->toThrow(InvalidArgumentException::class);
});
