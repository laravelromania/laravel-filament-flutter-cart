<div>
    @if ($this->orders->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-gray-500">Nu ai plasat încă nicio comandă.</p>
            <a href="{{ route('storefront.products') }}" wire:navigate class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Vezi produsele
            </a>
        </div>
    @else
        <ul class="mt-8 space-y-3">
            @foreach ($this->orders as $order)
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
                <li>
                    <a href="{{ route('storefront.account.order', ['number' => $order->number]) }}" wire:navigate
                        class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-6 py-4 hover:border-indigo-300 hover:shadow-sm">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-gray-900">{{ $order->number }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">{{ $order->status->label() }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ $order->created_at->format('d.m.Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-gray-900">{{ $order->total->format() }}</span>
                            <p class="mt-1 text-sm text-indigo-600">Vezi detalii →</p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
