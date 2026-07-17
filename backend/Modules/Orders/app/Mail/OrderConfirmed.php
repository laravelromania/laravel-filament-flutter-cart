<?php

declare(strict_types=1);

namespace Modules\Orders\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Orders\Models\Order;

/**
 * The "am primit comanda ta" email, queued from
 * {@see \Modules\Orders\Listeners\CreateOrderFromCheckout} right after the order
 * is persisted. Queued (ShouldQueue) so a slow mail transport never blocks the
 * checkout request; under the sync queue used in tests, Mail::fake records it.
 */
class OrderConfirmed extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmarea comenzii '.$this->order->number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'orders::mail.order-confirmed',
        );
    }
}
