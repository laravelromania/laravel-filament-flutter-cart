<div>
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Bine ai revenit, {{ $user->name }}!</h1>
    <p class="mt-2 text-gray-500">Aici îți gestionezi contul, adresele și comenzile.</p>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-3">
        <a href="{{ route('storefront.account.profile') }}" wire:navigate class="rounded-2xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Profil</h2>
            <p class="mt-1 text-sm text-gray-500">Nume, telefon și parolă.</p>
        </a>

        <a href="{{ route('storefront.account.addresses') }}" wire:navigate class="rounded-2xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Adrese</h2>
            <p class="mt-1 text-sm text-gray-500">Adresele tale de facturare și livrare.</p>
        </a>

        <a href="{{ route('storefront.account.orders') }}" wire:navigate class="rounded-2xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Comenzi</h2>
            <p class="mt-1 text-sm text-gray-500">Istoricul comenzilor tale.</p>
        </a>
    </div>
</div>
