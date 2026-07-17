<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Catalog\Livewire\SearchBox;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('redirects to the products listing carrying the search term', function () {
    Livewire::test(SearchBox::class)
        ->set('q', 'telefon')
        ->call('search')
        ->assertRedirect(route('storefront.products', ['search' => 'telefon']));
});

it('redirects to the bare products listing when the term is empty', function () {
    Livewire::test(SearchBox::class)
        ->set('q', '')
        ->call('search')
        ->assertRedirect(route('storefront.products'));
});
