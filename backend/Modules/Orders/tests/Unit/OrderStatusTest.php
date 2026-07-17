<?php

declare(strict_types=1);

use Modules\Orders\Enums\OrderStatus;

it('exposes the seven order states', function () {
    expect(array_map(fn (OrderStatus $s) => $s->value, OrderStatus::cases()))
        ->toBe(['pending', 'paid', 'fulfilled', 'shipped', 'completed', 'cancelled', 'refunded']);
});

it('gives every state a Romanian label and a Filament colour', function (OrderStatus $status) {
    expect($status->label())->toBeString()->not->toBe('');
    expect($status->color())->toBeIn(['gray', 'danger', 'info', 'primary', 'success', 'warning']);
})->with(OrderStatus::cases());

it('allows only the documented forward transitions', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Paid))->toBeTrue();
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Cancelled))->toBeTrue();
    expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Fulfilled))->toBeTrue();
    expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Refunded))->toBeTrue();
    expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Shipped))->toBeTrue();
    expect(OrderStatus::Shipped->canTransitionTo(OrderStatus::Completed))->toBeTrue();
});

it('rejects transitions that are not in the graph', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Shipped))->toBeFalse();
    expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Shipped))->toBeFalse();
    expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Pending))->toBeFalse();
    expect(OrderStatus::Completed->canTransitionTo(OrderStatus::Paid))->toBeFalse();
    expect(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Paid))->toBeFalse();
    expect(OrderStatus::Refunded->canTransitionTo(OrderStatus::Paid))->toBeFalse();
});

it('treats completed, cancelled and refunded as terminal', function (OrderStatus $terminal) {
    expect($terminal->allowedTransitions())->toBe([]);
    expect($terminal->transitions())->toBe([]);
})->with([OrderStatus::Completed, OrderStatus::Cancelled, OrderStatus::Refunded]);

it('maps allowed transitions to a value=>label list for the Filament select', function () {
    expect(OrderStatus::Paid->transitions())->toBe([
        'fulfilled' => OrderStatus::Fulfilled->label(),
        'refunded' => OrderStatus::Refunded->label(),
    ]);
});
