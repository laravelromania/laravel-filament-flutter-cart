<div class="mx-auto max-w-md">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Autentificare</h1>
    <p class="mt-2 text-sm text-gray-500">
        Nu ai cont?
        <a href="{{ route('register') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500">Creează unul</a>
    </p>

    <form wire:submit="login" class="mt-8 space-y-5 rounded-2xl border border-gray-200 bg-white p-6">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
                type="email" id="email" wire:model="email" autocomplete="email"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Parolă</label>
            <input
                type="password" id="password" wire:model="password" autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Ține-mă minte
        </label>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
        >Autentificare</button>
    </form>
</div>
