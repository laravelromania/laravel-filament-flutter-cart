import 'package:flutter/material.dart';

import '../models/checkout_result.dart';
import 'orders_screen.dart';

/// Ecran de confirmare după `POST /checkout` — numărul comenzii, totalul și,
/// dacă gateway-ul ales cere o plată online (Netopia/PayU, nu ramburs/„mock"),
/// URL-ul de redirecționare întors de server.
///
/// O aplicație completă ar deschide acel URL într-un `WebView` sau în
/// browserul extern (pachetul `url_launcher`); seria nu adaugă acea
/// dependență (pubspec-ul rămâne `http` + `provider` + `flutter_secure_storage`),
/// așa că aici URL-ul e doar afișat, selectabil pentru copiere.
class OrderConfirmationScreen extends StatelessWidget {
  const OrderConfirmationScreen({super.key, required this.result});

  final CheckoutResult result;

  @override
  Widget build(BuildContext context) {
    final order = result.order;
    final payment = result.payment;

    return Scaffold(
      appBar: AppBar(title: const Text('Comandă plasată'), automaticallyImplyLeading: false),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const Icon(Icons.check_circle_outline, color: Colors.green, size: 56),
          const SizedBox(height: 16),
          Text(
            'Comanda ${order.number} a fost plasată.',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          const SizedBox(height: 8),
          Text('Total: ${order.total.formatted}'),
          Text('Livrare: ${order.shippingLabel} (${order.shippingTotal.formatted})'),
          if (payment != null) ...[
            const SizedBox(height: 24),
            Text('Plată online necesară', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            const Text('Deschide acest link pentru a finaliza plata:'),
            const SizedBox(height: 4),
            SelectableText(payment.url, style: const TextStyle(color: Colors.blue)),
          ],
          const SizedBox(height: 32),
          FilledButton(
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const OrdersScreen()),
            ),
            child: const Text('Vezi comenzile mele'),
          ),
          const SizedBox(height: 8),
          OutlinedButton(
            onPressed: () => Navigator.of(context).popUntil((route) => route.isFirst),
            child: const Text('Înapoi la catalog'),
          ),
        ],
      ),
    );
  }
}
