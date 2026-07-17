<div>
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Coșul tău</h1>

    @if ($this->data->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-gray-500">Coșul tău este gol.</p>
            <a
                href="{{ route('storefront.products') }}"
                wire:navigate
                class="mt-4 inline-block rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
            >Vezi produsele</a>
        </div>
    @else
        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <ul class="divide-y divide-gray-100 rounded-2xl border border-gray-200 bg-white">
                    @foreach ($this->data->lines as $line)
                        <li wire:key="row-{{ $line->variantId }}" class="flex items-center gap-4 p-4">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $line->name }}</p>
                                <p class="mt-0.5 text-sm text-gray-500">{{ $line->unitPrice->format() }} / buc</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="updateQty('{{ $line->variantId }}', {{ $line->quantity - 1 }})"
                                    class="flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-50"
                                    aria-label="Scade cantitatea"
                                >&minus;</button>
                                <span class="w-10 text-center">{{ $line->quantity }}</span>
                                <button
                                    type="button"
                                    wire:click="updateQty('{{ $line->variantId }}', {{ $line->quantity + 1 }})"
                                    class="flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-50"
                                    aria-label="Crește cantitatea"
                                >+</button>
                            </div>

                            <div class="w-28 text-right font-semibold text-gray-900">{{ $line->lineTotal->format() }}</div>

                            <button
                                type="button"
                                wire:click="remove('{{ $line->variantId }}')"
                                class="text-sm text-gray-400 hover:text-red-600"
                            >Șterge</button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Sumar</h2>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="text-gray-600">Subtotal ({{ $this->data->itemCount }} produse)</span>
                        <span class="font-semibold text-gray-900">{{ $this->data->subtotal->format() }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Livrarea și taxele se calculează la finalizare.</p>
                    <button
                        type="button"
                        class="mt-6 w-full rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
                    >Spre finalizare</button>
                    {{-- Finalizarea comenzii (checkout) sosește în Partea 8. --}}
                </div>
            </aside>
        </div>
    @endif
</div>
