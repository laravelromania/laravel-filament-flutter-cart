<div class="max-w-lg">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Profilul meu</h1>

    @if ($status)
        <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ $status }}</div>
    @endif

    <form wire:submit="save" class="mt-8 space-y-5 rounded-2xl border border-gray-200 bg-white p-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nume</label>
            <input
                type="text" id="name" wire:model="name"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
            <input
                type="text" id="phone" wire:model="phone"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <hr class="border-gray-100">

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Parolă nouă <span class="text-gray-400">(lasă gol dacă nu o schimbi)</span></label>
            <input
                type="password" id="password" wire:model="password" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmă parola nouă</label>
            <input
                type="password" id="password_confirmation" wire:model="password_confirmation" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
        </div>

        <button
            type="submit"
            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
        >Salvează</button>
    </form>
</div>
