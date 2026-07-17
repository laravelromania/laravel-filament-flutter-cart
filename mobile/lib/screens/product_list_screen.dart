import 'package:flutter/material.dart';

import '../api_service.dart';
import '../models/product.dart';
import 'product_detail_screen.dart';

/// Ecranul de start: catalogul de produse, sub formă de grid.
///
/// `GET /api/v1/products` e un endpoint public — nu are nevoie de token, deci
/// funcționează chiar înainte de a exista autentificare (Partea 14). Folosește
/// [FutureBuilder] pentru cele trei stări (încărcare/eroare/date) și
/// [RefreshIndicator] pentru „tragere pentru reîmprospătare".
class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key});

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final ApiService _api = ApiService();
  late Future<List<Product>> _future;

  @override
  void initState() {
    super.initState();
    _future = _api.products();
  }

  Future<void> _refresh() async {
    final next = _api.products();
    setState(() => _future = next);
    // RefreshIndicator ascultă acest Future ca să știe când să ascundă spinner-ul.
    await next.catchError((_) => <Product>[]);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Catalog produse')),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: FutureBuilder<List<Product>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }

            if (snapshot.hasError) {
              return _ScrollableMessage(
                child: _ErrorView(message: '${snapshot.error}', onRetry: _refresh),
              );
            }

            final products = snapshot.data ?? const <Product>[];

            if (products.isEmpty) {
              return const _ScrollableMessage(
                child: Text('Niciun produs găsit.'),
              );
            }

            return GridView.builder(
              padding: const EdgeInsets.all(12),
              physics: const AlwaysScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 0.66,
              ),
              itemCount: products.length,
              itemBuilder: (context, index) => _ProductCard(product: products[index]),
            );
          },
        ),
      ),
    );
  }
}

/// Wrapper derulabil, necesar ca `RefreshIndicator` să funcționeze și pe stări
/// de eroare/gol (are nevoie de un `Scrollable` dedesubt ca să detecteze gestul).
class _ScrollableMessage extends StatelessWidget {
  const _ScrollableMessage({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: SizedBox(
          height: constraints.maxHeight,
          child: Center(child: child),
        ),
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  const _ProductCard({required this.product});

  final Product product;

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => ProductDetailScreen(slug: product.slug),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: product.image != null
                  ? Image.network(product.image!, fit: BoxFit.cover)
                  : const ColoredBox(
                      color: Color(0xFFEEEEEE),
                      child: Center(
                        child: Icon(Icons.image_not_supported_outlined, size: 40),
                      ),
                    ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 8, 0),
              child: Text(
                product.name,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 4, 8, 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    // Money.formatted vine deja gata formatat de server — nicio
                    // conversie/calcul monetar pe client (vezi lib/models/money.dart).
                    product.price.formatted,
                    style: Theme.of(context)
                        .textTheme
                        .titleSmall
                        ?.copyWith(fontWeight: FontWeight.bold),
                  ),
                  if (!product.inStock)
                    const Text(
                      'Stoc epuizat',
                      style: TextStyle(color: Colors.redAccent, fontSize: 11),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  const _ErrorView({required this.message, required this.onRetry});

  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.error_outline, size: 40, color: Colors.redAccent),
          const SizedBox(height: 12),
          Text('Nu am putut încărca produsele.\n$message', textAlign: TextAlign.center),
          const SizedBox(height: 12),
          ElevatedButton(onPressed: onRetry, child: const Text('Încearcă din nou')),
        ],
      ),
    );
  }
}
