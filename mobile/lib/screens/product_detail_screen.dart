import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../api_service.dart';
import '../models/product_detail.dart';
import '../state/auth_provider.dart';
import '../state/cart_provider.dart';
import 'login_screen.dart';

/// Ecranul de detaliu produs: galerie de imagini, selector de variantă și
/// prețul curent (via `Money.formatted`, fără calcule pe client).
///
/// Butonul „Adaugă în coș" era inert în Partea 13 — acum e cablat la
/// `CartProvider.add()` (`POST /api/v1/cart`, endpoint autentificat). Dacă
/// utilizatorul nu e logat, apăsarea deschide `LoginScreen` (care oferă și
/// creare de cont); la o autentificare reușită — login SAU register — adaugă
/// automat produsul care a declanșat fluxul.
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

  /// Adaugă `variantId` în coș prin `CartProvider`. Cere autentificare: dacă
  /// tokenul lipsește, deschide `LoginScreen` și, la o autentificare reușită
  /// (login sau cont nou), continuă automat cu adăugarea — utilizatorul nu
  /// trebuie să apese „Adaugă în coș" a doua oară.
  Future<void> _addToCart(BuildContext context, int variantId) async {
    if (!context.read<AuthProvider>().isAuthed) {
      final loggedIn = await Navigator.of(context).push<bool>(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      if (loggedIn != true || !context.mounted) return;
    }

    final cart = context.read<CartProvider>();
    await cart.add(variantId: variantId, qty: 1);
    if (!context.mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(cart.error ?? 'Adăugat în coș.')),
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
          // Coșul lucrează strict la nivel de variantă (`POST /cart` cere
          // `variantId`) — dacă produsul n-are nicio variantă (caz limită,
          // teoretic posibil per modelul de date din Partea 4), nu avem ce
          // trimite, deci butonul rămâne dezactivat.
          final variantId = variant?.id;

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
                onPressed: (inStock && variantId != null)
                    ? () => _addToCart(context, variantId)
                    : null,
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
