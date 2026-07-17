<div
    x-data="{ open: false }"
    x-on:open-cart.window="open = true"
    x-on:keydown.escape.window="open = false"
>
    {{-- Overlay --}}
    <div
        x-show="open"
        x-transition.opacity
        @click="open = false"
        class="fixed inset-0 z-40 bg-gray-900/40"
        style="display: none;"
    ></div>

    {{-- Panou glisant --}}
    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white shadow-xl"
        style="display: none;"
        role="dialog"
        aria-label="Coș de cumpărături"
    >
        <header class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Coșul tău</h2>
            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600" aria-label="Închide">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            @if ($this->data->isEmpty())
                <p class="mt-10 text-center text-sm text-gray-500">Coșul tău este gol.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($this->data->lines as $line)
                        <li wire:key="line-{{ $line->variantId }}" class="flex gap-3 py-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $line->name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $line->unitPrice->format() }} / buc</p>

                                <div class="mt-2 flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="updateQty('{{ $line->variantId }}', {{ $line->quantity - 1 }})"
                                        class="flex h-7 w-7 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-50"
                                        aria-label="Scade cantitatea"
                                    >&minus;</button>
                                    <span class="w-8 text-center text-sm">{{ $line->quantity }}</span>
                                    <button
                                        type="button"
                                        wire:click="updateQty('{{ $line->variantId }}', {{ $line->quantity + 1 }})"
                                        class="flex h-7 w-7 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-50"
                                        aria-label="Crește cantitatea"
                                    >+</button>
                                </div>
                            </div>

                            <div class="flex flex-col items-end justify-between">
                                <span class="text-sm font-semibold text-gray-900">{{ $line->lineTotal->format() }}</span>
                                <button
                                    type="button"
                                    wire:click="remove('{{ $line->variantId }}')"
                                    class="text-xs text-gray-400 hover:text-red-600"
                                >Șterge</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <footer class="border-t border-gray-200 px-5 py-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-lg font-semibold text-gray-900">{{ $this->data->subtotal->format() }}</span>
            </div>
            <div class="mt-4 flex flex-col gap-2">
                <a
                    href="{{ route('storefront.cart') }}"
                    wire:navigate
                    @click="open = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50"
                >Vezi coșul</a>
                @if (\Illuminate\Support\Facades\Route::has('storefront.checkout'))
                    <a
                        href="{{ route('storefront.checkout') }}"
                        wire:navigate
                        @click="open = false"
                        @class([
                            'rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-500',
                            'pointer-events-none opacity-50' => $this->data->isEmpty(),
                        ])
                        @if ($this->data->isEmpty()) aria-disabled="true" tabindex="-1" @endif
                    >Spre finalizare</a>
                @else
                    <button
                        type="button"
                        @disabled($this->data->isEmpty())
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                    >Spre finalizare</button>
                    {{-- Finalizarea comenzii (checkout) sosește în Partea 8. --}}
                @endif
            </div>
        </footer>
    </aside>
</div>
