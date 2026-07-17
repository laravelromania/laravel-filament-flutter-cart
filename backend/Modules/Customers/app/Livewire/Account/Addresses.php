<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Customers\Livewire\Concerns\ResolvesCustomer;
use Modules\Customers\Models\Address;

/**
 * Address book CRUD at `/cont/adrese`: list, create, edit, delete, set
 * default. Only one address may be default per customer, enforced here (not
 * in the database) by clearing every sibling's flag inside a transaction
 * whenever a new one is marked default.
 */
#[Layout('core::layouts.storefront')]
#[Title('Adresele mele')]
class Addresses extends Component
{
    use ResolvesCustomer;

    public bool $showForm = false;

    /** @var array<string, mixed> */
    public array $form = [
        'id' => null,
        'type' => 'shipping',
        'name' => '',
        'phone' => '',
        'county' => '',
        'city' => '',
        'street' => '',
        'postal_code' => '',
        'is_default' => false,
    ];

    #[Computed]
    public function addresses(): Collection
    {
        return $this->customer()->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $addressId): void
    {
        $address = $this->customer()->addresses()->findOrFail($addressId);

        $this->form = $address->only([
            'id', 'type', 'name', 'phone', 'county', 'city', 'street', 'postal_code', 'is_default',
        ]);
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'form.type' => ['required', Rule::in(['billing', 'shipping'])],
            'form.name' => ['required', 'string', 'max:255'],
            'form.phone' => ['required', 'string', 'max:30'],
            'form.county' => ['required', 'string', 'max:100'],
            'form.city' => ['required', 'string', 'max:100'],
            'form.street' => ['required', 'string', 'max:255'],
            'form.postal_code' => ['required', 'string', 'max:20'],
        ])['form'];

        $customer = $this->customer();
        $addressId = $this->form['id'] ?? null;
        $isDefault = (bool) $this->form['is_default'];

        DB::transaction(function () use ($customer, $addressId, $validated, $isDefault) {
            if ($addressId) {
                $address = $customer->addresses()->findOrFail($addressId);
                $address->fill($validated);
            } else {
                $address = new Address($validated);
                $customer->addresses()->save($address);
            }

            if ($isDefault) {
                $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->is_default = $isDefault;
            $address->save();
        });

        unset($this->addresses);
        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $addressId): void
    {
        $this->customer()->addresses()->where('id', $addressId)->delete();

        unset($this->addresses);
    }

    public function setDefault(int $addressId): void
    {
        $customer = $this->customer();

        DB::transaction(function () use ($customer, $addressId) {
            $customer->addresses()->update(['is_default' => false]);
            $customer->addresses()->where('id', $addressId)->update(['is_default' => true]);
        });

        unset($this->addresses);
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->form = [
            'id' => null,
            'type' => 'shipping',
            'name' => '',
            'phone' => '',
            'county' => '',
            'city' => '',
            'street' => '',
            'postal_code' => '',
            'is_default' => false,
        ];
    }

    public function render(): View
    {
        return view('customers::livewire.account.addresses');
    }
}
