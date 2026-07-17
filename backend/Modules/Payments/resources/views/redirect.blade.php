@extends('core::layouts.storefront')

@section('title', 'Redirecționare către procesatorul de plăți')

@section('content')
    <div class="mx-auto max-w-md text-center">
        <h1 class="text-xl font-semibold text-gray-900">Te redirecționăm către procesatorul de plăți…</h1>
        <p class="mt-2 text-sm text-gray-500">Dacă nu ești redirecționat automat, apasă butonul de mai jos.</p>

        {{-- Gateway-urile care cer form-POST (ex. Netopia) primesc câmpurile
             ascunse construite de driver și trimit formularul automat. --}}
        <form id="payment-redirect" method="POST" action="{{ $redirect->url }}" class="mt-6">
            @foreach ($redirect->fields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <button type="submit"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                Continuă către plată
            </button>
        </form>
    </div>

    <script>
        document.getElementById('payment-redirect').submit();
    </script>
@endsection
