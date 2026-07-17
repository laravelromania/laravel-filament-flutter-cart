<?php

declare(strict_types=1);

namespace Modules\Orders\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Orders\Models\Order;

/**
 * Renders an order's invoice to a PDF (dompdf) and streams it as a download.
 *
 * The same route serves both audiences: staff (admin/manager role) may download
 * any invoice, a signed-in shopper only their own. The illustrative VAT line in
 * the Blade template is exactly that — illustrative, not a real fiscal document.
 */
class InvoiceController
{
    public function __invoke(string $number): Response
    {
        $order = Order::where('number', $number)->firstOrFail();

        $user = Auth::user();

        $isStaff = $user !== null
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin', 'manager']);

        $isOwner = $user !== null
            && $order->user_id !== null
            && (int) $order->user_id === (int) $user->getAuthIdentifier();

        abort_unless($isStaff || $isOwner, 403);

        $order->load('items');

        return Pdf::loadView('orders::invoice', ['order' => $order])
            ->download('factura-'.$order->number.'.pdf');
    }
}
