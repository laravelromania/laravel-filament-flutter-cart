<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cart\Services\DatabaseCart;
use Modules\Cart\Services\SessionCart;
use Modules\Core\Contracts\CartRepository;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('resolves SessionCart for a guest', function () {
    expect(app(CartRepository::class))->toBeInstanceOf(SessionCart::class);
});

it('resolves DatabaseCart for an authenticated user', function () {
    $this->actingAs(User::factory()->create());

    expect(app(CartRepository::class))->toBeInstanceOf(DatabaseCart::class);
});
