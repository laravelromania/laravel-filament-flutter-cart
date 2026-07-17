<div class="w-full max-w-xs">
    <form wire:submit="search" class="relative">
        <input
            type="search"
            wire:model="q"
            placeholder="Caută produse…"
            aria-label="Caută produse"
            class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-9 text-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
        <button
            type="submit"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-600"
            aria-label="Caută"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </button>
    </form>
</div>
