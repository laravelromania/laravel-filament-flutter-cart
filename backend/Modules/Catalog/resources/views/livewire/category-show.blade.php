<div>
    <nav class="mb-4 text-sm text-gray-500">
        <a href="{{ route('storefront.products') }}" wire:navigate class="hover:text-indigo-600">Produse</a>
        <span class="mx-1">/</span>
        <span class="text-gray-700">{{ $category->name }}</span>
    </nav>

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $category->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $products->total() }} {{ $products->total() === 1 ? 'produs' : 'produse' }} în această categorie.</p>
        </div>

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
