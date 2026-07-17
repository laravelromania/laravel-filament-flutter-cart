import 'package:flutter/material.dart';

import 'screens/product_list_screen.dart';

void main() {
  runApp(const MagazinApp());
}

/// Rădăcina aplicației. În Partea 14 se adaugă `MultiProvider` (auth +
/// cart) în jurul lui `home`; catalogul rămâne accesibil fără autentificare.
class MagazinApp extends StatelessWidget {
  const MagazinApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Magazin modular',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.indigo),
        useMaterial3: true,
      ),
      home: const ProductListScreen(),
    );
  }
}
