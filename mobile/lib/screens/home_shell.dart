import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../state/auth_provider.dart';
import '../state/cart_provider.dart';
import 'cart_screen.dart';
import 'login_screen.dart';
import 'orders_screen.dart';
import 'product_list_screen.dart';

/// Rădăcina navigării, odată ce `MultiProvider` (main.dart) a pus
/// `AuthProvider` și `CartProvider` deasupra întregului arbore. Trei file:
/// catalogul (mereu public), coșul și contul — ultimele două cer
/// autentificare, dar NU blochează pornirea aplicației: catalogul rămâne
/// prima filă, mereu accesibilă, exact cum cere specificația („browse
/// catalog freely; cart/checkout require login").
class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    final itemCount = context.watch<CartProvider>().itemCount;

    return Scaffold(
      body: IndexedStack(
        index: _index,
        children: const [
          ProductListScreen(),
          _CartTab(),
          _AccountTab(),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (index) => setState(() => _index = index),
        destinations: [
          const NavigationDestination(icon: Icon(Icons.storefront_outlined), label: 'Catalog'),
          NavigationDestination(
            icon: Badge(
              label: Text('$itemCount'),
              isLabelVisible: itemCount > 0,
              child: const Icon(Icons.shopping_cart_outlined),
            ),
            label: 'Coș',
          ),
          const NavigationDestination(icon: Icon(Icons.person_outline), label: 'Cont'),
        ],
      ),
    );
  }
}

/// Fila „Coș": arată [CartScreen] doar dacă utilizatorul e autentificat —
/// altfel un prompt de login, ca să nu lovim direct un endpoint
/// `auth:sanctum` fără token (ar întoarce 401 oricum, dar experiența e mai
/// bună dacă cerem autentificarea înainte, nu după un apel eșuat).
class _CartTab extends StatelessWidget {
  const _CartTab();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    if (auth.bootstrapping) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return auth.isAuthed
        ? const CartScreen()
        : const _AuthGate(message: 'Autentifică-te ca să vezi coșul.');
  }
}

class _AccountTab extends StatelessWidget {
  const _AccountTab();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    if (auth.bootstrapping) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (!auth.isAuthed) {
      return const _AuthGate(message: 'Autentifică-te ca să vezi contul și comenzile.');
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Contul meu')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(auth.user?.name ?? '', style: Theme.of(context).textTheme.titleLarge),
          Text(auth.user?.email ?? '', style: Theme.of(context).textTheme.bodyMedium),
          const SizedBox(height: 24),
          ListTile(
            leading: const Icon(Icons.receipt_long_outlined),
            title: const Text('Comenzile mele'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const OrdersScreen()),
            ),
          ),
          const SizedBox(height: 24),
          OutlinedButton.icon(
            onPressed: () async {
              final cart = context.read<CartProvider>();
              await context.read<AuthProvider>().logout();
              cart.clearLocal();
            },
            icon: const Icon(Icons.logout),
            label: const Text('Deconectare'),
          ),
        ],
      ),
    );
  }
}

/// Prompt de autentificare, arătat în locul unui ecran care cere login.
class _AuthGate extends StatelessWidget {
  const _AuthGate({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.lock_outline, size: 48),
              const SizedBox(height: 12),
              Text(message, textAlign: TextAlign.center),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                ),
                child: const Text('Autentificare'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
