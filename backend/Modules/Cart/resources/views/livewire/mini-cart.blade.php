<div>
    <button
        type="button"
        x-data
        @click="$dispatch('open-cart')"
        class="relative inline-flex items-center gap-2 text-gray-600 hover:text-indigo-600"
        aria-label="Deschide coșul de cumpărături"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-.534 1.872-1.719 1.872-3.062V6.75a.75.75 0 0 0-.75-.75H5.106" />
        </svg>
        <span class="hidden sm:inline">Coș</span>
        <span
            class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-indigo-600 px-1.5 py-0.5 text-xs font-semibold text-white"
        >{{ $itemCount }}</span>
    </button>
</div>
