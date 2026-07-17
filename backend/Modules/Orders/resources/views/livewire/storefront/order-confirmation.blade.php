<div class="mx-auto max-w-3xl">
    @php $order = $this->order(); @endphp
    @php
        $paid = $order->paid_at !== null;
        $badge = match ($order->status->color()) {
            'success' => 'bg-green-100 text-green-700',
            'warning' => 'bg-amber-100 text-amber-700',
            'danger' => 'bg-red-100 text-red-700',
            'info' => 'bg-sky-100 text-sky-700',
            'primary' => 'bg-indigo-100 text-indigo-700',
            default => 'bg-gray-100 text-gray-700',
        };
    @endphp

    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full {{ $paid ? 'bg-green-100' : 'bg-indigo-100' }}">
            @if ($paid)
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            @else
                <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            @endif
        </div>
        <h1 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Îți mulțumim!</h1>
        <p class="mt-3 text-gray-600">
            Comanda <span class="font-semibold text-gray-900">{{ $order->number }}</span> a fost înregistrată.
            @unless ($paid)
                Finalizează plata pentru a o procesa.
            @endunless
        </p>
        <span class="mt-4 inline-block rounded-full px-3 py-1 text-sm font-medium {{ $badge }}">{{ $order->status->label() }}</span>
    </div>

    @if ($this->canPay())
        <div class="mt-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-6 text-center">
            <p class="text-gray-700">Comanda ta așteaptă plata online.</p>
            <a href="{{ $this->payUrl() }}"
                class="mt-4 inline-block rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-500">
                Plătește {{ $order->total->format() }}
            </a>
        </div>
    @endif

    <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white">
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

    <div class="mt-8 text-center">
        <a href="{{ url('/') }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
            Înapoi la magazin
        </a>
    </div>
</div>
