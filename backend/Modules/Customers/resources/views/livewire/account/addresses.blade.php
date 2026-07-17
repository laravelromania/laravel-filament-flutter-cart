<div>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Adresele mele</h1>
        @unless ($showForm)
            <button
                type="button" wire:click="create"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
            >Adaugă adresă</button>
        @endunless
    </div>

    @if ($showForm)
        <form wire:submit="save" class="mt-6 space-y-5 rounded-2xl border border-gray-200 bg-white p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tip</label>
                    <select
                        wire:model="form.type"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="shipping">Livrare</option>
                        <option value="billing">Facturare</option>
                    </select>
                    @error('form.type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nume destinatar</label>
                    <input
                        type="text" wire:model="form.name"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('form.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefon</label>
                    <input
                        type="text" wire:model="form.phone"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('form.phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Județ</label>
                    <input
                        type="text" wire:model="form.county"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('form.county') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Oraș</label>
                    <input
                        type="text" wire:model="form.city"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('form.city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Cod poștal</label>
                    <input
                        type="text" wire:model="form.postal_code"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('form.postal_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Stradă și număr</label>
                    <input
                        type="text" wire:model="form.street"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('form.street') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model="form.is_default" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Setează ca adresă implicită
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Salvează adresa</button>
                <button type="button" wire:click="cancel" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Renunță</button>
            </div>
        </form>
    @endif

    <div class="mt-8 space-y-4">
        @forelse ($this->addresses as $address)
            <div wire:key="address-{{ $address->id }}" class="flex items-start justify-between rounded-2xl border border-gray-200 bg-white p-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                            {{ $address->type === 'billing' ? 'Facturare' : 'Livrare' }}
                        </span>
                        @if ($address->is_default)
                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Implicită</span>
                        @endif
                    </div>
                    <p class="mt-2 font-medium text-gray-900">{{ $address->name }} · {{ $address->phone }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $address->street }}, {{ $address->city }}, jud. {{ $address->county }}, {{ $address->postal_code }}</p>
                </div>

                <div class="flex shrink-0 flex-col items-end gap-2 text-sm">
                    <button type="button" wire:click="edit({{ $address->id }})" class="text-indigo-600 hover:text-indigo-500">Editează</button>
                    @unless ($address->is_default)
                        <button type="button" wire:click="setDefault({{ $address->id }})" class="text-gray-500 hover:text-indigo-600">Setează implicită</button>
                    @endunless
                    <button
                        type="button" wire:click="delete({{ $address->id }})"
                        wire:confirm="Ștergi această adresă?"
                        class="text-red-500 hover:text-red-600"
                    >Șterge</button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <p class="text-gray-500">Nu ai nicio adresă salvată încă.</p>
            </div>
        @endforelse
    </div>
</div>
