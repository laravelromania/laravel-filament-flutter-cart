@extends('core::layouts.storefront')

@section('title', 'Plată simulată')

@section('content')
    <div class="mx-auto max-w-xl text-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Plată simulată</h1>
        <p class="mt-3 text-gray-600">
            Aceasta este o pagină de test către care redirecționează gateway-ul mock.
            Procesatorul real de plăți (Netopia / PayU) apare în Partea 11 a seriei.
        </p>
        @if (request('ref'))
            <p class="mt-4 text-sm text-gray-500">Referință: {{ request('ref') }}</p>
        @endif
        <a href="{{ route('storefront.checkout.confirmation') }}" wire:navigate
            class="mt-8 inline-block rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            Confirmă plata (simulat)
        </a>
    </div>
@endsection
