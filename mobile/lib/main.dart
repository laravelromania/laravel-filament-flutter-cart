import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'api_service.dart';
import 'screens/home_shell.dart';
import 'state/auth_provider.dart';
import 'state/cart_provider.dart';

void main() {
  final api = ApiService();
  final authProvider = AuthProvider(api: api);

  // Un 401 pe orice cerere autentificată înseamnă că tokenul salvat nu mai e
  // valid (revocat, expirat) — curăță starea locală fără alt apel de rețea
  // care ar eșua la fel (vezi `ApiService.onUnauthorized`).
  api.onUnauthorized = authProvider.forceLogout;

  // Nu blocăm primul frame după token: `bootstrap()` rulează în fundal, iar
  // `AuthProvider.bootstrapping` ține ecranele la curent cât timp se încarcă
  // (vezi `HomeShell`, care arată un spinner în locul promptului de login).
  authProvider.bootstrap();

  runApp(MagazinApp(api: api, authProvider: authProvider));
}

/// Rădăcina aplicației. `MultiProvider` pune `AuthProvider` și `CartProvider`
/// deasupra întregului arbore de widget-uri — orice ecran le poate citi prin
/// `context.watch`/`context.read`, fără să treacă starea manual prin
/// constructori. Catalogul rămâne accesibil fără autentificare; doar coșul și
/// contul cer login (vezi `HomeShell`).
class MagazinApp extends StatelessWidget {
  const MagazinApp({super.key, required this.api, required this.authProvider});

  final ApiService api;
  final AuthProvider authProvider;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        ChangeNotifierProvider<CartProvider>(create: (_) => CartProvider(api: api)),
      ],
      child: MaterialApp(
        title: 'Magazin modular',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(seedColor: Colors.indigo),
          useMaterial3: true,
        ),
        home: const HomeShell(),
      ),
    );
  }
}
