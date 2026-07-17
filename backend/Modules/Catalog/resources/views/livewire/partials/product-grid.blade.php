{{--
    Shared product grid, reused by ProductIndex and CategoryShow.
    Expects: $products (LengthAwarePaginator of Product, with brand/variants/media
    eager-loaded by the component).
--}}
<div>
    <div wire:loading.delay.flex class="mb-4 items-center gap-2 text-sm text-gray-500">
        <svg class="h-4 w-4 animate-spin text-indigo-600" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
        </svg>
        Se încarcă produsele…
    </div>

    @if ($products->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500">
            Niciun produs nu corespunde filtrelor selectate.
        </div>
    @else
        <div
            wire:loading.class="opacity-40"
            class="grid grid-cols-1 gap-6 transition-opacity sm:grid-cols-2 lg:grid-cols-3"
        >
            @foreach ($products as $product)
                @php($image = $product->getFirstMediaUrl('images'))
                <a
                    wire:key="product-{{ $product->id }}"
                    href="{{ route('storefront.product', ['product' => $product->slug]) }}"
                    wire:navigate
                    class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
                >
                    <div class="aspect-square w-full overflow-hidden bg-gray-100">
                        @if ($image)
                            <img
                                src="{{ $image }}"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            >
                        @else
                            <div class="flex h-full w-full items-center justify-center text-gray-300">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col gap-1 p-4">
                        @if ($product->brand)
                            <span class="text-xs font-medium uppercase tracking-wide text-indigo-600">{{ $product->brand->name }}</span>
                        @endif
                        <h3 class="font-medium text-gray-900 group-hover:text-indigo-600">{{ $product->name }}</h3>
                        <div class="mt-auto flex items-center justify-between pt-3">
                            <span class="text-lg font-semibold text-gray-900">{{ $product->displayPrice()->format() }}</span>
                            @unless ($product->inStock())
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Stoc epuizat</span>
                            @endunless
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
