import 'package:flutter/material.dart';

import '../api_service.dart';
import '../models/order.dart';

/// Detaliul unei comenzi — `GET /api/v1/orders/{number}`, la fel de „scoped"
/// pe utilizator ca lista (o comandă a altcuiva întoarce 404, nu 403 — vezi
/// `OrderController::show`, ca numerele de comandă să nu fie enumerabile
/// între conturi).
class OrderDetailScreen extends StatefulWidget {
  const OrderDetailScreen({super.key, required this.number});

  final String number;

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  final _api = ApiService();
  late final Future<OrderDetail> _future;

  @override
  void initState() {
    super.initState();
    _future = _api.orderByNumber(widget.number);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Comanda ${widget.number}')),
      body: FutureBuilder<OrderDetail>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(child: Text('Eroare: ${snapshot.error}'));
          }

          final order = snapshot.data;
          if (order == null) {
            return const Center(child: Text('Comanda nu a fost găsită.'));
          }

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Text(order.status.label, style: Theme.of(context).textTheme.titleMedium),
              if (order.createdAt != null) ...[
                const SizedBox(height: 4),
                Text(_formatDate(order.createdAt!)),
              ],
              const Divider(height: 32),
              Text('Produse', style: Theme.of(context).textTheme.titleMedium),
              for (final item in order.items)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(child: Text('${item.name} x${item.quantity}')),
                      Text(item.lineTotal.formatted),
                    ],
                  ),
                ),
              const Divider(height: 32),
              _totalRow(context, 'Subtotal produse', order.itemsSubtotal.formatted),
              _totalRow(context, order.shippingLabel, order.shippingTotal.formatted),
              _totalRow(context, 'Total', order.total.formatted, emphasize: true),
              const Divider(height: 32),
              Text('Adresă de livrare', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 4),
              Text(order.shipping.name),
              Text(order.shipping.street),
              Text('${order.shipping.city}, ${order.shipping.county}, ${order.shipping.postalCode}'),
              Text(order.shipping.phone),
              if (order.awb != null) ...[
                const SizedBox(height: 16),
                Text('AWB curier: ${order.awb}'),
              ],
            ],
          );
        },
      ),
    );
  }

  Widget _totalRow(BuildContext context, String label, String value, {bool emphasize = false}) {
    final style = emphasize
        ? Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)
        : Theme.of(context).textTheme.bodyMedium;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [Text(label, style: style), Text(value, style: style)],
      ),
    );
  }

  /// Fără `intl` în dependințe (serverul deja formatează banii, vezi
  /// `models/money.dart`) — un format simplu zi.lună.an e suficient aici.
  String _formatDate(DateTime date) {
    return '${date.day.toString().padLeft(2, '0')}.${date.month.toString().padLeft(2, '0')}.${date.year}';
  }
}
