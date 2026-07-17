<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Factură {{ $order->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; margin: 0; padding: 32px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #6b7280; }
        .header { width: 100%; margin-bottom: 24px; }
        .header td { vertical-align: top; }
        .parties { width: 100%; margin-bottom: 24px; }
        .parties td { vertical-align: top; width: 50%; padding-right: 12px; }
        .box-title { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { text-align: left; border-bottom: 2px solid #111827; padding: 6px 4px; font-size: 11px; }
        table.items td { border-bottom: 1px solid #e5e7eb; padding: 6px 4px; }
        .right { text-align: right; }
        .center { text-align: center; }
        table.totals { width: 40%; margin-left: 60%; border-collapse: collapse; }
        table.totals td { padding: 4px; }
        table.totals tr.grand td { font-weight: bold; border-top: 2px solid #111827; font-size: 14px; }
        .note { margin-top: 32px; padding: 12px; background: #f9fafb; border-radius: 6px; color: #6b7280; font-size: 11px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>Factură (demonstrativă)</h1>
                <div class="muted">Seria CMD nr. {{ $order->number }}</div>
                <div class="muted">Data: {{ $order->created_at?->format('d.m.Y') }}</div>
            </td>
            <td class="right">
                <strong>{{ config('app.name', 'Magazin') }}</strong><br>
                <span class="muted">Proiect educațional</span>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td>
                <div class="box-title">Facturat către</div>
                <strong>{{ $order->billing['name'] ?? $order->customer_name }}</strong><br>
                {{ $order->billing['street'] ?? '' }}<br>
                {{ $order->billing['city'] ?? '' }}, {{ $order->billing['county'] ?? '' }}
                {{ $order->billing['postalCode'] ?? '' }}<br>
                {{ $order->email }} · {{ $order->phone }}
            </td>
            <td>
                <div class="box-title">Livrare către</div>
                <strong>{{ $order->shipping['name'] ?? $order->customer_name }}</strong><br>
                {{ $order->shipping['street'] ?? '' }}<br>
                {{ $order->shipping['city'] ?? '' }}, {{ $order->shipping['county'] ?? '' }}
                {{ $order->shipping['postalCode'] ?? '' }}<br>
                Metodă: {{ $order->shipping_label }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Produs</th>
                <th class="center">Cant.</th>
                <th class="right">Preț unitar</th>
                <th class="right">Total linie</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ $item->unit_price->format() }}</td>
                    <td class="right">{{ $item->line_total->format() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal produse</td>
            <td class="right">{{ $order->items_subtotal->format() }}</td>
        </tr>
        <tr>
            <td>Livrare</td>
            <td class="right">{{ $order->shipping_total->format() }}</td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="right">{{ $order->total->format() }}</td>
        </tr>
    </table>

    <div class="note">
        Document demonstrativ generat automat de un magazin educațional. TVA-ul, acolo
        unde ar apărea, este pur ilustrativ — aceasta NU este o factură fiscală validă
        și nu are valoare contabilă.
    </div>
</body>
</html>
