<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmarea comenzii {{ $order->number }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border-radius:12px;padding:32px;">
            <h1 style="margin:0 0 8px;font-size:22px;">Îți mulțumim pentru comandă!</h1>
            <p style="margin:0 0 24px;color:#4b5563;">
                Am înregistrat comanda <strong>{{ $order->number }}</strong> pe numele
                {{ $order->customer_name }}. Mai jos găsești un rezumat.
            </p>

            <table style="width:100%;border-collapse:collapse;margin-bottom:24px;">
                <thead>
                    <tr>
                        <th align="left" style="padding:8px 0;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Produs</th>
                        <th align="center" style="padding:8px 0;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Cant.</th>
                        <th align="right" style="padding:8px 0;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td style="padding:8px 0;border-bottom:1px solid #f3f4f6;">{{ $item->name }}</td>
                            <td align="center" style="padding:8px 0;border-bottom:1px solid #f3f4f6;">{{ $item->quantity }}</td>
                            <td align="right" style="padding:8px 0;border-bottom:1px solid #f3f4f6;">{{ $item->line_total->format() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:4px 0;color:#4b5563;">Subtotal produse</td>
                    <td align="right" style="padding:4px 0;">{{ $order->items_subtotal->format() }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;color:#4b5563;">Livrare ({{ $order->shipping_label }})</td>
                    <td align="right" style="padding:4px 0;">{{ $order->shipping_total->format() }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;font-weight:bold;border-top:1px solid #e5e7eb;">Total</td>
                    <td align="right" style="padding:8px 0;font-weight:bold;border-top:1px solid #e5e7eb;">{{ $order->total->format() }}</td>
                </tr>
            </table>

            <p style="margin:24px 0 0;font-size:13px;color:#9ca3af;">
                Acesta este un magazin demonstrativ dintr-o serie de tutoriale. Nu se
                procesează plăți sau livrări reale.
            </p>
        </div>
    </div>
</body>
</html>
