import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/cart.dart';
import '../state/cart_provider.dart';
import 'checkout_screen.dart';

/// Coșul curent al utilizatorului autentificat — citește starea din
/// `CartProvider` (Provider), nu face niciun apel API direct. Fiecare
/// modificare (cantitate, ștergere) trece prin provider, care reapelează
/// API-ul și notifică ascultătorii cu coșul întreg întors de server.
class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  @override
  void initState() {
    super.initState();
    // Coșul e populat lazy — abia când utilizatorul chiar deschide fila
    // „Coș" facem primul `GET /cart`, nu la pornirea aplicației.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) context.read<CartProvider>().refresh();
    });
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Coșul meu')),
      body: RefreshIndicator(
        onRefresh: () => context.read<CartProvider>().refresh(),
        child: _buildBody(context, cart),
      ),
    );
  }

  Widget _buildBody(BuildContext context, CartProvider cart) {
    if (cart.loading && cart.lines.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (cart.error != null && cart.lines.isEmpty) {
      return _ScrollableCenter(
        child: Text('Nu am putut încărca coșul.\n${cart.error}', textAlign: TextAlign.center),
      );
    }

    if (cart.isEmpty) {
      return const _ScrollableCenter(child: Text('Coșul e gol momentan.'));
    }

    return Column(
      children: [
        Expanded(
          child: ListView.separated(
            padding: const EdgeInsets.all(12),
            itemCount: cart.lines.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (context, index) => _CartLineTile(line: cart.lines[index]),
          ),
        ),
        _CartSummary(cart: cart),
      ],
    );
  }
}

class _CartLineTile extends StatelessWidget {
  const _CartLineTile({required this.line});

  final CartLine line;

  @override
  Widget build(BuildContext context) {
    final cart = context.read<CartProvider>();

    return ListTile(
      title: Text(line.name),
      // Money.formatted vine deja gata formatat de server (vezi
      // lib/models/money.dart) — nicio conversie/calcul monetar pe client.
      subtitle: Text('${line.unitPrice.formatted} x ${line.quantity}'),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          IconButton(
            icon: const Icon(Icons.remove_circle_outline),
            onPressed: line.quantity > 1
                ? () => cart.updateQty(variantId: line.variantId, qty: line.quantity - 1)
                : null,
          ),
          Text('${line.quantity}'),
          IconButton(
            icon: const Icon(Icons.add_circle_outline),
            onPressed: () => cart.updateQty(variantId: line.variantId, qty: line.quantity + 1),
          ),
          IconButton(
            icon: const Icon(Icons.delete_outline),
            onPressed: () => cart.remove(variantId: line.variantId),
          ),
        ],
      ),
    );
  }
}

class _CartSummary extends StatelessWidget {
  const _CartSummary({required this.cart});

  final CartProvider cart;

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      minimum: const EdgeInsets.all(16),
      child: Row(
        children: [
          Expanded(
            child: Text(
              'Subtotal: ${cart.subtotal?.formatted ?? '-'}',
              style: Theme.of(context).textTheme.titleMedium,
            ),
          ),
          FilledButton(
            onPressed: cart.isEmpty
                ? null
                : () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                    ),
            child: const Text('Spre checkout'),
          ),
        ],
      ),
    );
  }
}

/// Wrapper derulabil, necesar ca `RefreshIndicator` să funcționeze și pe stări
/// de eroare/gol (are nevoie de un `Scrollable` dedesubt ca să detecteze
/// gestul) — același truc ca în `product_list_screen.dart` (Partea 13).
class _ScrollableCenter extends StatelessWidget {
  const _ScrollableCenter({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: SizedBox(
          height: constraints.maxHeight,
          child: Center(child: Padding(padding: const EdgeInsets.all(24), child: child)),
        ),
      ),
    );
  }
}
