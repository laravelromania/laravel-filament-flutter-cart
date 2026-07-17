<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Modules\Core\ValueObjects\Money;

/**
 * The storefront landing page. Kept trivial for Part 2 — its only job is to prove
 * the storefront layout boots. Real listings arrive with the Catalog module.
 */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('core::home', [
            'money' => Money::of(12990),
        ]);
    }
}
