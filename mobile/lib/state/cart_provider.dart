import 'package:flutter/foundation.dart';

import '../api_service.dart';
import '../models/cart.dart';
import '../models/money.dart';

/// Starea coșului, partajată în toată aplicația prin `Provider`. Fiecare
/// metodă face un apel API care întoarce coșul ÎNTREG (nu doar linia
/// modificată — așa răspunde `CartResource` la orice mutație, vezi
/// `api_service.dart`), așa că nu există nicio actualizare optimistă/parțială
/// pe client: [lines]/[subtotal]/[itemCount] sunt mereu exact ce a spus
/// ultima dată serverul.
class CartProvider extends ChangeNotifier {
  CartProvider({required ApiService api}) : _api = api;

  final ApiService _api;

  CartData? _cart;
  bool _loading = false;
  String? _error;

  int get itemCount => _cart?.itemCount ?? 0;
  Money? get subtotal => _cart?.subtotal;
  List<CartLine> get lines => _cart?.lines ?? const [];
  bool get isEmpty => lines.isEmpty;
  bool get loading => _loading;

  /// Mesajul ultimului eșec de rețea, dacă a fost unul — ecranele îl arată
  /// direct (`ApiException.message`), fără să mai prindă excepția ele însele.
  String? get error => _error;

  /// `GET /cart` — reîncarcă starea de la zero (folosit la deschiderea
  /// ecranului de coș și pentru „tragere pentru reîmprospătare").
  Future<void> refresh() => _run(() => _api.cartGet());

  /// `POST /cart` — adaugă `qty` bucăți din variantă.
  Future<void> add({required int variantId, required int qty}) =>
      _run(() => _api.cartAdd(variantId: variantId, qty: qty));

  /// `PATCH /cart/{variantId}` — suprascrie cantitatea unei linii existente.
  Future<void> updateQty({required int variantId, required int qty}) =>
      _run(() => _api.cartUpdate(variantId: variantId, qty: qty));

  /// `DELETE /cart/{variantId}` — scoate linia din coș.
  Future<void> remove({required int variantId}) => _run(() => _api.cartRemove(variantId: variantId));

  /// Golește coșul ȚINUT LOCAL, FĂRĂ niciun apel de rețea — folosit după un
  /// checkout reușit (serverul a golit deja coșul prin `PlaceOrder`, Partea
  /// 8/9, deci un `refresh()` ar întoarce oricum un coș gol) și la
  /// deconectare, ca „Coșul meu" să nu mai arate produsele fostului utilizator
  /// dacă un altul se autentifică pe același dispozitiv.
  void clearLocal() {
    _cart = null;
    _error = null;
    notifyListeners();
  }

  Future<void> _run(Future<CartData> Function() call) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      _cart = await call();
    } on ApiException catch (e) {
      _error = e.message;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }
}
