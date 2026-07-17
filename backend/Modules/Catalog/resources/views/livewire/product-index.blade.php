<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Produse</h1>
        <p class="mt-1 text-sm text-gray-500">Explorează catalogul: filtrează după categorie, brand și atribute, caută și sortează.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
        {{-- Bara de filtre --}}
        <aside class="space-y-6">
            <div>
                <label for="filter-search" class="mb-1 block text-sm font-medium text-gray-700">Căutare</label>
                <input
                    id="filter-search"
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Nume produs…"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="filter-category" class="mb-1 block text-sm font-medium text-gray-700">Categorie</label>
                <select
                    id="filter-category"
                    wire:model.live="category"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Toate categoriile</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-brand" class="mb-1 block text-sm font-medium text-gray-700">Brand</label>
                <select
                    id="filter-brand"
                    wire:model.live="brand"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Toate brandurile</option>
                    @foreach ($brands as $b)
                        <option value="{{ $b->slug }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            @foreach ($filterAttributes as $attribute)
                @if ($attribute->values->isNotEmpty())
                    <div>
                        <span class="mb-2 block text-sm font-medium text-gray-700">{{ $attribute->name }}</span>
                        <div class="space-y-1">
                            @foreach ($attribute->values as $value)
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input
                                        type="checkbox"
                                        value="{{ $value->slug }}"
                                        wire:model.live="attributeFilters"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    {{ $value->value }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            <div>
                <span class="mb-2 block text-sm font-medium text-gray-700">Preț (lei)</span>
                <div class="flex items-center gap-2">
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        wire:model.live.debounce.500ms="priceMin"
                        placeholder="min"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <span class="text-gray-400">–</span>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        wire:model.live.debounce.500ms="priceMax"
                        placeholder="max"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>

            <button
                type="button"
                wire:click="clearFilters"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
            >
                Resetează filtrele
            </button>
        </aside>

        {{-- Rezultate --}}
        <div>
            <div class="mb-4 flex items-center justify-between gap-4">
                <p class="text-sm text-gray-500">{{ $products->total() }} {{ $products->total() === 1 ? 'produs' : 'produse' }}</p>
                <div class="flex items-center gap-2">
                    <label for="sort" class="text-sm text-gray-500">Sortează</label>
                    <select
                        id="sort"
                        wire:model.live="sort"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="nou">Cele mai noi</option>
                        <option value="pret-asc">Preț crescător</option>
                        <option value="pret-desc">Preț descrescător</option>
                        <option value="nume">Nume (A–Z)</option>
                    </select>
                </div>
            </div>

            @include('catalog::livewire.partials.product-grid', ['products' => $products])
        </div>
    </div>
</div>
