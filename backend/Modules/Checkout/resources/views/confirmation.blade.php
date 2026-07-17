@extends('core::layouts.storefront')

@section('title', 'Comandă plasată')

@section('content')
    <div class="mx-auto max-w-xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </div>
        <h1 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Îți mulțumim!</h1>
        <p class="mt-3 text-gray-600">
            Comanda ta a fost înregistrată. Vei primi în curând un e-mail cu detaliile.
        </p>
        <a href="{{ url('/') }}" wire:navigate
            class="mt-8 inline-block rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            Înapoi la magazin
        </a>
    </div>
@endsection
