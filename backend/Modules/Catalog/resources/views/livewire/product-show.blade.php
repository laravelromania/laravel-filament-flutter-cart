<div>
    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('storefront.products') }}" wire:navigate class="hover:text-indigo-600">Produse</a>
        <span class="mx-1">/</span>
        <span class="text-gray-700">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
        {{-- Galerie --}}
        @php($media = $product->getMedia('images'))
        <div x-data="{ active: '{{ $media->first()?->getUrl() }}' }">
            <div class="aspect-square w-full overflow-hidden rounded-2xl border border-gray-200 bg-gray-100">
                @if ($media->isNotEmpty())
                    <img :src="active" alt="{{ $product->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-gray-300">
                        <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                        </svg>
                    </div>
                @endif
            </div>

            @if ($media->count() > 1)
                <div class="mt-4 flex gap-3">
                    @foreach ($media as $image)
                        <button
                            type="button"
                            @click="active = '{{ $image->getUrl() }}'"
                            :class="active === '{{ $image->getUrl() }}' ? 'ring-2 ring-indigo-600' : 'ring-1 ring-gray-200'"
                            class="h-16 w-16 overflow-hidden rounded-lg bg-gray-100"
                        >
                            <img src="{{ $image->getUrl() }}" alt="" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Informații + acțiuni --}}
        <div>
            @if ($product->brand)
                <span class="text-sm font-medium uppercase tracking-wide text-indigo-600">{{ $product->brand->name }}</span>
            @endif
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-900">{{ $product->name }}</h1>

            <div class="mt-4 flex items-center gap-3">
                <span class="text-3xl font-semibold text-gray-900">
                    {{ ($this->variant?->effectivePrice() ?? $product->price)->format() }}
                </span>
                @if ($this->variant)
                    @if ($this->variant->inStock())
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">În stoc</span>
                    @else
                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Stoc epuizat</span>
                    @endif
                @endif
            </div>

            @if ($product->description)
                <p class="mt-6 text-gray-600">{{ $product->description }}</p>
            @endif

            {{-- Selectorul de variantă --}}
            @if ($product->variants->isNotEmpty())
                <div class="mt-8">
                    <span class="mb-2 block text-sm font-medium text-gray-700">Variantă</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($product->variants as $variant)
                            @php($label = $variant->attributeValues->pluck('value')->join(' · ') ?: $variant->sku)
                            <button
                                type="button"
                                wire:key="variant-{{ $variant->id }}"
                                wire:click="selectVariant({{ $variant->id }})"
                                @class([
                                    'rounded-lg border px-4 py-2 text-sm transition',
                                    'border-indigo-600 bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600' => $selectedVariant === $variant->id,
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $selectedVariant !== $variant->id,
                                    'cursor-not-allowed opacity-50' => ! $variant->inStock(),
                                ])
                                @disabled(! $variant->inStock())
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Cantitate + adăugare în coș --}}
                <div class="mt-8 flex items-end gap-4">
                    <div>
                        <label for="qty" class="mb-1 block text-sm font-medium text-gray-700">Cantitate</label>
                        <input
                            id="qty"
                            type="number"
                            min="1"
                            wire:model="qty"
                            class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    <button
                        type="button"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        @disabled(! ($this->variant?->inStock() ?? false))
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="addToCart">Adaugă în coș</span>
                        <span wire:loading wire:target="addToCart">Se adaugă…</span>
                    </button>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    Butonul emite evenimentul Livewire <code>add-to-cart</code>; coșul care îl ascultă sosește în Partea 6.
                </p>
            @else
                <p class="mt-8 text-sm text-gray-500">Acest produs nu are variante disponibile.</p>
            @endif
        </div>
    </div>

    {{-- Toast: ascultă evenimentul emis de addToCart --}}
    <div
        x-data="{ show: false, message: '', timer: null }"
        x-on:cart-toast.window="message = $event.detail.message; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, 2500)"
    >
        <div
            x-show="show"
            x-transition
            style="display: none;"
            class="fixed bottom-6 right-6 z-50 rounded-lg bg-gray-900 px-4 py-3 text-sm font-medium text-white shadow-lg"
        >
            <span x-text="message"></span>
        </div>
    </div>
</div>
