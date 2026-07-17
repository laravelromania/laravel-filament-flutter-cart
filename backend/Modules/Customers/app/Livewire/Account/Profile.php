<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Customers\Livewire\Concerns\ResolvesCustomer;

/**
 * Edits the account holder's name (on `users`) and phone (on `customers`) in
 * one form, plus an optional password change. Password fields are left blank
 * on mount and only touched if the visitor fills them in.
 */
#[Layout('core::layouts.storefront')]
#[Title('Profilul meu')]
class Profile extends Component
{
    use ResolvesCustomer;

    public string $name = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $status = null;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->phone = (string) ($this->customer()->phone ?? '');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->name = $validated['name'];

        if (filled($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->customer()->update(['phone' => $validated['phone'] ?: null]);

        $this->password = '';
        $this->password_confirmation = '';
        $this->status = 'Profilul a fost actualizat.';
    }

    public function render(): View
    {
        return view('customers::livewire.account.profile');
    }
}
