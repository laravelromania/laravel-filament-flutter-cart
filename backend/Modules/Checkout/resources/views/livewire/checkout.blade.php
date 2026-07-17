<div class="mx-auto max-w-4xl">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Finalizare comandă</h1>

    {{-- Indicator de pași --}}
    @php
        $steps = [
            \Modules\Checkout\Livewire\Checkout::STEP_CART => 'Coș',
            \Modules\Checkout\Livewire\Checkout::STEP_ADDRESS => 'Adresă',
            \Modules\Checkout\Livewire\Checkout::STEP_SHIPPING => 'Livrare',
            \Modules\Checkout\Livewire\Checkout::STEP_PAYMENT => 'Plată',
            \Modules\Checkout\Livewire\Checkout::STEP_SUMMARY => 'Sumar',
        ];
    @endphp
    <ol class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
        @foreach ($steps as $number => $label)
            <li @class([
                'flex items-center gap-2',
                'font-semibold text-indigo-600' => $step === $number,
                'text-gray-400' => $step < $number,
                'text-gray-700' => $step > $number,
            ])>
                <span @class([
                    'flex h-6 w-6 items-center justify-center rounded-full text-xs',
                    'bg-indigo-600 text-white' => $step >= $number,
                    'bg-gray-200 text-gray-500' => $step < $number,
                ])>{{ $number }}</span>
                {{ $label }}
                @if (! $loop->last)<span class="text-gray-300">&rarr;</span>@endif
            </li>
        @endforeach
    </ol>

    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Pasul 1: recapitularea coșului                                    --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($step === \Modules\Checkout\Livewire\Checkout::STEP_CART)
            <h2 class="text-lg font-semibold text-gray-900">Coșul tău</h2>
            <ul class="mt-4 divide-y divide-gray-100">
                @foreach ($this->cartData->lines as $line)
                    <li wire:key="cart-{{ $line->variantId }}" class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $line->name }}</p>
                            <p class="text-sm text-gray-500">{{ $line->quantity }} &times; {{ $line->unitPrice->format() }}</p>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $line->lineTotal->format() }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4">
                <span class="text-gray-600">Subtotal ({{ $this->cartData->itemCount }} produse)</span>
                <span class="text-lg font-semibold text-gray-900">{{ $this->cartData->subtotal->format() }}</span>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="toAddress"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Continuă spre adresă
                </button>
            </div>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pasul 2: adresa și datele de contact                              --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($step === \Modules\Checkout\Livewire\Checkout::STEP_ADDRESS)
            @php $showForm = $this->newAddress || $this->addresses->isEmpty(); @endphp
            <h2 class="text-lg font-semibold text-gray-900">Date de contact</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" wire:model="email"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nume complet</label>
                    <input type="text" wire:model="customerName"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @error('customerName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefon</label>
                    <input type="text" wire:model="phone"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">Adresă de livrare</h2>

            @if ($this->addresses->isNotEmpty())
                <div class="mt-4 space-y-3">
                    @foreach ($this->addresses as $address)
                        <label wire:key="addr-{{ $address->id }}" @class([
                            'flex cursor-pointer items-start gap-3 rounded-lg border p-4',
                            'border-indigo-500 ring-1 ring-indigo-500' => ! $this->newAddress && $this->shippingAddressId === $address->id,
                            'border-gray-200' => $this->newAddress || $this->shippingAddressId !== $address->id,
                        ])>
                            <input type="radio" wire:click="chooseAddress({{ $address->id }})"
                                @checked(! $this->newAddress && $this->shippingAddressId === $address->id)
                                class="mt-1">
                            <span class="text-sm text-gray-700">
                                <span class="font-medium text-gray-900">{{ $address->name }}</span><br>
                                {{ $address->street }}, {{ $address->city }}, {{ $address->county }} {{ $address->postal_code }}<br>
                                {{ $address->phone }}
                            </span>
                        </label>
                    @endforeach
                    <button type="button" wire:click="addNewAddress"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        + Adaugă o adresă nouă
                    </button>
                </div>
                @error('shippingAddressId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @endif

            @if ($showForm)
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nume destinatar</label>
                        <input type="text" wire:model="ship.name"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('ship.name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefon destinatar</label>
                        <input type="text" wire:model="ship.phone"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('ship.phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Județ</label>
                        <input type="text" wire:model="ship.county"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('ship.county') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Oraș</label>
                        <input type="text" wire:model="ship.city"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('ship.city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Stradă și număr</label>
                        <input type="text" wire:model="ship.street"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('ship.street') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cod poștal</label>
                        <input type="text" wire:model="ship.postal_code"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('ship.postal_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div class="mt-6 flex items-center justify-between">
                <button type="button" wire:click="back" class="text-sm font-medium text-gray-500 hover:text-gray-700">&larr; Înapoi</button>
                <button type="button" wire:click="toShipping"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Continuă spre livrare
                </button>
            </div>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pasul 3: metoda de livrare                                        --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($step === \Modules\Checkout\Livewire\Checkout::STEP_SHIPPING)
            <h2 class="text-lg font-semibold text-gray-900">Metodă de livrare</h2>
            <div class="mt-4 space-y-3">
                @foreach ($this->shippingOptions as $option)
                    <label wire:key="ship-{{ $option['code'] }}" @class([
                        'flex cursor-pointer items-center justify-between rounded-lg border p-4',
                        'border-indigo-500 ring-1 ring-indigo-500' => $shippingCode === $option['code'],
                        'border-gray-200' => $shippingCode !== $option['code'],
                    ])>
                        <span class="flex items-center gap-3 text-sm text-gray-700">
                            <input type="radio" wire:model="shippingCode" value="{{ $option['code'] }}">
                            {{ $option['label'] }}
                        </span>
                        <span class="font-semibold text-gray-900">{{ $option['cost']->format() }}</span>
                    </label>
                @endforeach
            </div>
            @error('shippingCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="mt-6 flex items-center justify-between">
                <button type="button" wire:click="back" class="text-sm font-medium text-gray-500 hover:text-gray-700">&larr; Înapoi</button>
                <button type="button" wire:click="toPayment"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Continuă spre plată
                </button>
            </div>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pasul 4: metoda de plată                                          --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($step === \Modules\Checkout\Livewire\Checkout::STEP_PAYMENT)
            <h2 class="text-lg font-semibold text-gray-900">Metodă de plată</h2>
            <div class="mt-4 space-y-3">
                @foreach ($this->paymentOptions as $option)
                    <label wire:key="pay-{{ $option['code'] }}" @class([
                        'flex cursor-pointer items-center gap-3 rounded-lg border p-4 text-sm text-gray-700',
                        'border-indigo-500 ring-1 ring-indigo-500' => $paymentCode === $option['code'],
                        'border-gray-200' => $paymentCode !== $option['code'],
                    ])>
                        <input type="radio" wire:model="paymentCode" value="{{ $option['code'] }}">
                        {{ $option['label'] }}
                    </label>
                @endforeach
            </div>
            @error('paymentCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="mt-6 flex items-center justify-between">
                <button type="button" wire:click="back" class="text-sm font-medium text-gray-500 hover:text-gray-700">&larr; Înapoi</button>
                <button type="button" wire:click="toSummary"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Continuă spre sumar
                </button>
            </div>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pasul 5: sumarul comenzii                                         --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($step === \Modules\Checkout\Livewire\Checkout::STEP_SUMMARY)
            <h2 class="text-lg font-semibold text-gray-900">Sumar comandă</h2>
            <ul class="mt-4 divide-y divide-gray-100">
                @foreach ($this->cartData->lines as $line)
                    <li wire:key="sum-{{ $line->variantId }}" class="flex items-center justify-between py-3">
                        <span class="text-sm text-gray-700">{{ $line->quantity }} &times; {{ $line->name }}</span>
                        <span class="font-medium text-gray-900">{{ $line->lineTotal->format() }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-2 border-t border-gray-200 pt-4 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Subtotal produse</dt>
                    <dd class="text-gray-900">{{ $this->totals['itemsSubtotal']->format() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Livrare ({{ $this->totals['shippingLabel'] }})</dt>
                    <dd class="text-gray-900">{{ $this->totals['shippingCost']->format() }}</dd>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-semibold">
                    <dt class="text-gray-900">Total</dt>
                    <dd class="text-gray-900">{{ $this->totals['total']->format() }}</dd>
                </div>
            </dl>
            <div class="mt-6 flex items-center justify-between">
                <button type="button" wire:click="back" class="text-sm font-medium text-gray-500 hover:text-gray-700">&larr; Înapoi</button>
                <button type="button" wire:click="placeOrder" wire:loading.attr="disabled"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                    Plasează comanda
                </button>
            </div>
        @endif
    </div>
</div>
