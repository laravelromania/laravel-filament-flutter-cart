<div class="mx-auto max-w-md">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Creează cont</h1>
    <p class="mt-2 text-sm text-gray-500">
        Ai deja cont?
        <a href="{{ route('login') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500">Autentifică-te</a>
    </p>

    <form wire:submit="register" class="mt-8 space-y-5 rounded-2xl border border-gray-200 bg-white p-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nume</label>
            <input
                type="text" id="name" wire:model="name" autocomplete="name"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
                type="email" id="email" wire:model="email" autocomplete="email"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Telefon <span class="text-gray-400">(opțional)</span></label>
            <input
                type="text" id="phone" wire:model="phone" autocomplete="tel"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Parolă</label>
            <input
                type="password" id="password" wire:model="password" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmă parola</label>
            <input
                type="password" id="password_confirmation" wire:model="password_confirmation" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
        >Creează cont</button>
    </form>
</div>
