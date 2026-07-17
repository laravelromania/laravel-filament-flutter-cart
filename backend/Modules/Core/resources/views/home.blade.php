@extends('core::layouts.storefront')

@section('title', config('app.name', 'Magazin') . ' · Acasă')

@section('content')
    <section class="rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Magazin modular Laravel</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight text-gray-900">Fundația este pe picioare</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
            Modulul Core oferă spina dorsală comună — value object-ul {{ $money->format() }},
            contractele partajate, rolurile și settings. Catalogul, coșul și checkout-ul se
            construiesc peste el în părțile următoare.
        </p>
    </section>
@endsection
