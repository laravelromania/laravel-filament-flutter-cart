import 'package:flutter/material.dart';

import '../api_service.dart';
import '../models/product_detail.dart';

/// Ecranul de detaliu produs: galerie de imagini, selector de variantă și
/// prețul curent (via `Money.formatted`, fără calcule pe client).
///
/// Butonul „Adaugă în coș" e inert în Partea 13 — cablarea reală la
/// `POST /api/v1/cart` (care e un endpoint autentificat) vine în Partea 14,
/// odată cu `CartProvider` și `flutter_secure_storage`.
class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  final ApiService _api = ApiService();
  late Future<ProductDetail> _future;
  int _selectedVariant = 0;

  @override
  void initState() {
    super.initState();
    _future = _api.productBySlug(widget.slug);
  }

  void _addToCart(BuildContext context) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Coșul se leagă în Partea 14 a seriei.')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detaliu produs')),
      body: FutureBuilder<ProductDetail>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(child: Text('Eroare: ${snapshot.error}'));
          }

          final product = snapshot.data;
          if (product == null) {
            return const Center(child: Text('Produsul nu a fost găsit.'));
          }

          final hasVariants = product.variants.isNotEmpty;
          // _selectedVariant e mereu în limite: pornește la 0 (mereu un index
          // valid dacă există măcar o variantă) și se schimbă doar din
          // onSelected mai jos, cu un index din același product.variants.
          final selectedIndex = hasVariants ? _selectedVariant : 0;
          final variant = hasVariants ? product.variants[selectedIndex] : null;
          final price = variant?.price ?? product.price;
          final inStock = variant?.inStock ?? product.inStock;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _Gallery(images: product.images),
              const SizedBox(height: 16),
              Text(product.name, style: Theme.of(context).textTheme.headlineSmall),
              if (product.brand != null) ...[
                const SizedBox(height: 4),
                Text(product.brand!, style: Theme.of(context).textTheme.bodyMedium),
              ],
              const SizedBox(height: 8),
              Text(
                price.formatted,
                style: Theme.of(context)
                    .textTheme
                    .titleLarge
                    ?.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              if (hasVariants) ...[
                Text('Variantă', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    for (var index = 0; index < product.variants.length; index++)
                      ChoiceChip(
                        label: Text(
                          product.variants[index].label.isEmpty
                              ? product.variants[index].sku
                              : product.variants[index].label,
                        ),
                        selected: selectedIndex == index,
                        onSelected: (_) => setState(() => _selectedVariant = index),
                      ),
                  ],
                ),
                const SizedBox(height: 16),
              ],
              if (product.description != null) ...[
                Text('Descriere', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                Text(product.description!),
                const SizedBox(height: 16),
              ],
              if (!inStock)
                const Padding(
                  padding: EdgeInsets.only(bottom: 12),
                  child: Text(
                    'Stoc epuizat momentan.',
                    style: TextStyle(color: Colors.redAccent),
                  ),
                ),
              FilledButton.icon(
                onPressed: inStock ? () => _addToCart(context) : null,
                icon: const Icon(Icons.add_shopping_cart),
                label: const Text('Adaugă în coș'),
              ),
            ],
          );
        },
      ),
    );
  }
}

/// Galerie orizontală simplă (`PageView`) peste `product.images` — lista de
/// URL-uri întoarsă de `ProductDetailResource.images` (Partea 12).
class _Gallery extends StatelessWidget {
  const _Gallery({required this.images});

  final List<String> images;

  @override
  Widget build(BuildContext context) {
    if (images.isEmpty) {
      return const AspectRatio(
        aspectRatio: 1,
        child: ColoredBox(
          color: Color(0xFFEEEEEE),
          child: Center(child: Icon(Icons.image_not_supported_outlined, size: 56)),
        ),
      );
    }

    return AspectRatio(
      aspectRatio: 1,
      child: PageView.builder(
        itemCount: images.length,
        itemBuilder: (context, index) => Image.network(images[index], fit: BoxFit.cover),
      ),
    );
  }
}
