<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Customers\Models\Customer;

/**
 * Creates a plain storefront account: a `users` row with NO role assigned
 * (so the Part-2 `canAccessPanel` gate blocks it from `/admin`) plus a
 * `Customer` profile row, then signs the visitor in via the default `web`
 * guard. `Auth::login()` fires `Illuminate\Auth\Events\Login`, which is
 * exactly what the Part-6 `MergeGuestCart` listener listens for — so a guest
 * who filled their basket before registering keeps it.
 */
#[Layout('core::layouts.storefront')]
#[Title('Creează cont')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Customer::create([
            'user_id' => $user->id,
            'phone' => $validated['phone'] ?: null,
        ]);

        Auth::login($user);

        $this->redirect(route('storefront.account.dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('customers::livewire.auth.register');
    }
}
