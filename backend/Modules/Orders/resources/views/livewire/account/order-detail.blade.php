<div>
    @php $order = $this->order(); @endphp
    @php
        $badge = match ($order->status->color()) {
            'success' => 'bg-green-100 text-green-700',
            'warning' => 'bg-amber-100 text-amber-700',
            'danger' => 'bg-red-100 text-red-700',
            'info' => 'bg-sky-100 text-sky-700',
            'primary' => 'bg-indigo-100 text-indigo-700',
            default => 'bg-gray-100 text-gray-700',
        };
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('storefront.account.orders') }}" wire:navigate class="text-sm text-gray-500 hover:text-indigo-600">← Comenzile mele</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">Comanda {{ $order->number }}</h1>
            <p class="mt-1 text-gray-500">Plasată pe {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $badge }}">{{ $order->status->label() }}</span>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Livrare</h2>
            <p class="mt-2 font-medium text-gray-900">{{ $order->shipping['name'] ?? $order->customer_name }}</p>
            <p class="text-gray-600">{{ $order->shipping['street'] ?? '' }}</p>
            <p class="text-gray-600">{{ $order->shipping['city'] ?? '' }}, {{ $order->shipping['county'] ?? '' }} {{ $order->shipping['postalCode'] ?? '' }}</p>
            <p class="mt-2 text-sm text-gray-500">{{ $order->shipping_label }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Contact</h2>
            <p class="mt-2 text-gray-600">{{ $order->email }}</p>
            <p class="text-gray-600">{{ $order->phone }}</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3">Produs</th>
                    <th class="px-6 py-3 text-center">Cant.</th>
                    <th class="px-6 py-3 text-right">Preț unitar</th>
                    <th class="px-6 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($order->items as $item)
                    <tr>
                        <td class="px-6 py-4 text-gray-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 text-right text-gray-600">{{ $item->unit_price->format() }}</td>
                        <td class="px-6 py-4 text-right text-gray-900">{{ $item->line_total->format() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-gray-200 px-6 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal produse</dt><dd class="text-gray-900">{{ $order->items_subtotal->format() }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Livrare</dt><dd class="text-gray-900">{{ $order->shipping_total->format() }}</dd></div>
                <div class="flex justify-between border-t border-gray-200 pt-1 font-semibold"><dt class="text-gray-900">Total</dt><dd class="text-gray-900">{{ $order->total->format() }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('orders.invoice', ['number' => $order->number]) }}"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            Descarcă factura (PDF)
        </a>
    </div>
</div>
