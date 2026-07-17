@extends('core::layouts.storefront')

@section('title', 'Simulare plată')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Mediu de test — {{ $label }}</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">Simulează plata</h1>
            <p class="mt-2 text-sm text-gray-500">
                Fără cont de comerciant real. Butoanele de mai jos trimit un callback
                semnat corect către procesator, exact ca un IPN real.
            </p>
            <p class="mt-1 text-xs text-gray-400">Referință: {{ $reference }}</p>

            <div class="mt-6 space-y-3">
                {{-- Ambele formulare trimit către ruta de callback (scutită de CSRF,
                     ca un IPN server-to-server). Semnătura e calculată pe server. --}}
                <form method="POST" action="{{ $callbackUrl }}">
                    <input type="hidden" name="reference" value="{{ $reference }}">
                    <input type="hidden" name="status" value="confirmed">
                    <input type="hidden" name="signature" value="{{ $successSignature }}">
                    <button type="submit"
                        class="w-full rounded-lg bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-500">
                        Plătește (succes)
                    </button>
                </form>

                <form method="POST" action="{{ $callbackUrl }}">
                    <input type="hidden" name="reference" value="{{ $reference }}">
                    <input type="hidden" name="status" value="canceled">
                    <input type="hidden" name="signature" value="{{ $failSignature }}">
                    <button type="submit"
                        class="w-full rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Anulează (eșec)
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
