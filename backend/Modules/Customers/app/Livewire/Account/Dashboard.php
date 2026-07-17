<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The `/cont` landing page: a greeting plus quick links into the rest of the
 * account area. Guarded by the `auth` middleware in routes/web.php (not a
 * role) — any signed-in user may see it, staff included.
 */
#[Layout('core::layouts.storefront')]
#[Title('Contul meu')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('customers::livewire.account.dashboard', [
            'user' => Auth::user(),
        ]);
    }
}
