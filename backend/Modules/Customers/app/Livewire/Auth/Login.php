<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Storefront login against the default `web` guard (same `users` table the
 * admin panel uses — see the Part 7 article for why there is only one guard).
 * `Auth::attempt()` fires `Illuminate\Auth\Events\Login` on success, which is
 * what the Part-6 `MergeGuestCart` listener merges the guest cart on.
 * Throttled per email+IP, mirroring Laravel's own `ThrottlesLogins`.
 */
#[Layout('core::layouts.storefront')]
#[Title('Autentificare')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Prea multe încercări. Mai încearcă peste {$seconds} secunde.",
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Datele de autentificare nu se potrivesc.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // `session()` (the SessionManager) rather than `request()->session()` —
        // a Livewire full-page component mounted directly in a test harness
        // (Livewire::test()) doesn't always have a session bound onto the
        // current Request the way a routed HTTP call does, but the container's
        // session manager is always available once the app has booted.
        session()->regenerate();

        $this->redirect(route('storefront.account.dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('customers::livewire.auth.login');
    }
}
