import 'package:flutter/material.dart';

import '../api_service.dart';
import '../models/order.dart';
import 'order_detail_screen.dart';

/// Istoricul comenzilor utilizatorului autentificat — `GET /api/v1/orders`,
/// filtrat automat de server după `user_id` (vezi `OrderController::index`,
/// Partea 12): niciun risc ca un shopper să vadă comenzile altcuiva.
class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  final _api = ApiService();
  late Future<List<OrderSummary>> _future;

  @override
  void initState() {
    super.initState();
    _future = _api.orders();
  }

  Future<void> _refresh() async {
    final next = _api.orders();
    setState(() => _future = next);
    await next.catchError((_) => <OrderSummary>[]);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Comenzile mele')),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: FutureBuilder<List<OrderSummary>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }

            if (snapshot.hasError) {
              return _ScrollableMessage(child: Text('Eroare: ${snapshot.error}'));
            }

            final orders = snapshot.data ?? const <OrderSummary>[];

            if (orders.isEmpty) {
              return const _ScrollableMessage(child: Text('Nu ai plasat nicio comandă încă.'));
            }

            return ListView.separated(
              padding: const EdgeInsets.all(12),
              physics: const AlwaysScrollableScrollPhysics(),
              itemCount: orders.length,
              separatorBuilder: (_, __) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final order = orders[index];

                return ListTile(
                  title: Text(order.number),
                  subtitle: Text('${order.status.label} · ${order.shippingLabel}'),
                  trailing: Text(order.total.formatted),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => OrderDetailScreen(number: order.number)),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}

/// Wrapper derulabil pentru ca `RefreshIndicator` să funcționeze pe stări de
/// eroare/gol — același truc din `product_list_screen.dart` (Partea 13).
class _ScrollableMessage extends StatelessWidget {
  const _ScrollableMessage({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: SizedBox(height: constraints.maxHeight, child: Center(child: child)),
      ),
    );
  }
}
