<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A plain POST action, not a Livewire component — logging out has no state to
 * render, just a side effect and a redirect, so a full-page Livewire
 * component (which must render a view) would be the wrong tool. Lives beside
 * Login/Register for discoverability (`routes/web.php` wires it the same
 * way), matching the "POST -> Auth::logout -> redirect home" spec.
 */
class Logout
{
    public function __invoke(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.home');
    }
}
