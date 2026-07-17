import 'dart:convert';

import 'package:http/http.dart' as http;

import 'config.dart';
import 'models/cart.dart';
import 'models/category.dart';
import 'models/checkout_result.dart';
import 'models/order.dart';
import 'models/product.dart';
import 'models/product_detail.dart';
import 'models/shipping_method.dart';
import 'models/user.dart';
import 'state/token_storage.dart';

/// Excepție uniformă pentru orice eșec de rețea sau de răspuns al API-ului,
/// aruncată de fiecare metodă din [ApiService].
///
/// `fieldErrors` e populat doar pentru un 422 de validare — cheia e numele
/// câmpului, valoarea e lista de mesaje, exact ca shape-ul de eroare Laravel
/// `{ "message": ..., "errors": { "email": ["..."] } }` (vezi Partea 12).
class ApiException implements Exception {
  final int? statusCode;
  final String message;
  final Map<String, List<String>>? fieldErrors;

  const ApiException(this.message, {this.statusCode, this.fieldErrors});

  @override
  String toString() => 'ApiException($statusCode): $message';
}

/// Client HTTP subțire peste `/api/v1`. Toată logica de rețea trăiește aici —
/// ecranele nu construiesc niciodată un [Uri] și nu decodează direct JSON, ca
/// să rămână networking-ul izolat într-un singur loc.
///
/// Partea 13 acoperea doar catalogul, care e public (fără token). Partea 14
/// adaugă autentificarea: [_headers] atașează acum, acolo unde `auth: true`,
/// antetul `Authorization: Bearer <token>` — citit din `flutter_secure_storage`
/// prin [TokenStorage], niciodată dintr-un câmp ținut pe instanță (mai multe
/// ecrane instanțiază câte un `ApiService()` propriu; storage-ul e sursa unică
/// de adevăr, nu obiectul). Pe lângă catalog, mai jos sunt acum și
/// register/login/logout/me, coșul (persistat per-utilizator în baza de
/// date, vezi Partea 6) și checkout-ul/comenzile.
///
/// [onUnauthorized], dacă e setat (vezi `main.dart`), se apelează pe orice
/// răspuns 401 — semnul că tokenul salvat nu mai e valid (revocat sau expirat)
/// — ca `AuthProvider` să poată forța o deconectare locală fără alt apel de
/// rețea care ar eșua la fel.
class ApiService {
  ApiService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  /// Cablat din `main.dart` la `AuthProvider.forceLogout`. Rămâne `null` (nu
  /// se întâmplă nimic special) dacă nimeni nu-l setează — util și în teste.
  void Function()? onUnauthorized;

  Uri _uri(String path, [Map<String, dynamic>? query]) {
    final normalizedQuery = query == null
        ? null
        : query.map((key, value) => MapEntry(key, value.toString()));

    return Uri.parse('${Config.baseUrl}${Config.apiPrefix}/$path')
        .replace(queryParameters: normalizedQuery);
  }

  /// Antetele comune. Cu `auth: true`, citește tokenul din [TokenStorage] și,
  /// dacă există, atașează `Authorization: Bearer <token>` — dacă nu există
  /// (ex. un apel greșit pe un endpoint protejat înainte de login), cererea
  /// pleacă oricum, iar serverul răspunde cu 401 ca de obicei.
  Future<Map<String, String>> _headers({bool auth = false}) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (auth) {
      final token = await TokenStorage.read();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    return headers;
  }

  /// `GET /api/v1/products` — listare paginată, cu filtre opționale
  /// (`search`, `brand`, `category`, `price_min`, `price_max`, `sort`,
  /// `per_page`). Returnează doar pagina curentă de produse; ecranul de
  /// listare din Partea 13 afișează prima pagină (paginarea completă e lăsată
  /// ca temă pentru cititor).
  Future<List<Product>> products({Map<String, dynamic>? filters}) async {
    final response = await _client.get(_uri('products', filters), headers: await _headers());
    final body = _decodeMap(response);
    final data = body['data'] as List<dynamic>? ?? const [];

    return data.map((item) => Product.fromJson(item as Map<String, dynamic>)).toList();
  }

  /// `GET /api/v1/products/{slug}` — detaliu complet, cu variante și atribute.
  ///
  /// Ca orice resursă unică Laravel, răspunsul e învelit implicit în
  /// `{ "data": {...} }` (confirmat împotriva serverului real, nu doar din
  /// citirea codului) — la fel ca listele paginate de mai sus, care poartă
  /// același `data`.
  Future<ProductDetail> productBySlug(String slug) async {
    final response = await _client.get(_uri('products/$slug'), headers: await _headers());
    final body = _decodeMap(response);

    return ProductDetail.fromJson(body['data'] as Map<String, dynamic>);
  }

  /// `GET /api/v1/categories` — arborele complet de categorii active.
  Future<List<Category>> categories() async {
    final response = await _client.get(_uri('categories'), headers: await _headers());
    final body = _decodeMap(response);
    final data = body['data'] as List<dynamic>? ?? const [];

    return data.map((item) => Category.fromJson(item as Map<String, dynamic>)).toList();
  }

  // --- Autentificare (Partea 14) ------------------------------------------

  /// `POST /api/v1/register` — creează contul și întoarce direct un token
  /// (contul nou e automat autentificat, ca la login). Răspunsul NU e învelit
  /// în `data` — e un obiect plat `{ "token": ..., "user": {...} }`, spre
  /// deosebire de catalog (confirmat prin curl).
  Future<AuthResult> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final response = await _client.post(
      _uri('register'),
      headers: await _headers(),
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      }),
    );
    final body = _decodeMap(response);

    return AuthResult(
      token: body['token'] as String,
      user: AppUser.fromJson(body['user'] as Map<String, dynamic>),
    );
  }

  /// `POST /api/v1/login` — schimbă credențialele pe un token nou. `deviceName`
  /// e doar eticheta sub care apare tokenul în `personal_access_tokens`
  /// (Sanctum), utilă dacă utilizatorul se conectează de pe mai multe telefoane.
  Future<AuthResult> login({
    required String email,
    required String password,
    String deviceName = 'mobile',
  }) async {
    final response = await _client.post(
      _uri('login'),
      headers: await _headers(),
      body: jsonEncode({'email': email, 'password': password, 'device_name': deviceName}),
    );
    final body = _decodeMap(response);

    return AuthResult(
      token: body['token'] as String,
      user: AppUser.fromJson(body['user'] as Map<String, dynamic>),
    );
  }

  /// `POST /api/v1/logout` — revocă tokenul curent (`currentAccessToken`,
  /// Sanctum) pe server. Dacă tokenul era deja invalid, aruncă `ApiException`
  /// (401) ca orice alt apel autentificat — `AuthProvider.logout()` prinde
  /// asta și curăță oricum starea locală.
  Future<void> logout() async {
    final response = await _client.post(_uri('logout'), headers: await _headers(auth: true));
    _decodeMap(response);
  }

  /// `GET /api/v1/user` — profilul curent, folosit la pornirea aplicației ca
  /// să reîmprospătăm numele/emailul din tokenul salvat (vezi
  /// `AuthProvider.bootstrap`).
  Future<AppUser> me() async {
    final response = await _client.get(_uri('user'), headers: await _headers(auth: true));
    final body = _decodeMap(response);

    return AppUser.fromJson(body['data'] as Map<String, dynamic>);
  }

  // --- Coș (Partea 14, auth'd) --------------------------------------------

  /// `GET /api/v1/cart` — coșul persistat în baza de date pentru
  /// utilizatorul tokenului (`DatabaseCart`, Partea 6); supraviețuiește
  /// restart-ului aplicației și schimbării de dispozitiv.
  Future<CartData> cartGet() async {
    final response = await _client.get(_uri('cart'), headers: await _headers(auth: true));

    return CartData.fromJson(_decodeMap(response)['data'] as Map<String, dynamic>);
  }

  /// `POST /api/v1/cart` — adaugă `qty` bucăți din variantă; întoarce coșul
  /// ÎNTREG (nu doar linia adăugată), la fel ca fiecare mutație de mai jos.
  Future<CartData> cartAdd({required int variantId, required int qty}) async {
    final response = await _client.post(
      _uri('cart'),
      headers: await _headers(auth: true),
      body: jsonEncode({'variantId': variantId, 'qty': qty}),
    );

    return CartData.fromJson(_decodeMap(response)['data'] as Map<String, dynamic>);
  }

  /// `PATCH /api/v1/cart/{variantId}` — suprascrie cantitatea unei linii.
  Future<CartData> cartUpdate({required int variantId, required int qty}) async {
    final response = await _client.patch(
      _uri('cart/$variantId'),
      headers: await _headers(auth: true),
      body: jsonEncode({'qty': qty}),
    );

    return CartData.fromJson(_decodeMap(response)['data'] as Map<String, dynamic>);
  }

  /// `DELETE /api/v1/cart/{variantId}` — scoate linia din coș complet.
  Future<CartData> cartRemove({required int variantId}) async {
    final response = await _client.delete(_uri('cart/$variantId'), headers: await _headers(auth: true));

    return CartData.fromJson(_decodeMap(response)['data'] as Map<String, dynamic>);
  }

  // --- Checkout & comenzi (Partea 14, auth'd) -----------------------------

  /// `GET /api/v1/checkout/shipping-methods` — curierii înregistrați în
  /// `ShippingManager` (Partea 10), fiecare cu un cost calculat live pentru
  /// coșul curent (și, opțional, destinația — parametrii sunt opționali;
  /// serverul funcționează și fără ei, folosind doar greutatea coșului).
  Future<List<ShippingMethod>> shippingMethods({String? county, String? city, String? postalCode}) async {
    final response = await _client.get(
      _uri('checkout/shipping-methods', {
        if (county != null) 'county': county,
        if (city != null) 'city': city,
        if (postalCode != null) 'postal_code': postalCode,
      }),
      headers: await _headers(auth: true),
    );
    final data = _decodeMap(response)['data'] as List<dynamic>? ?? const [];

    return data.map((item) => ShippingMethod.fromJson(item as Map<String, dynamic>)).toList();
  }

  /// `POST /api/v1/checkout` — plasează comanda prin `PlaceOrder` (Partea 8),
  /// aceeași acțiune folosită și de wizard-ul Livewire de pe magazinul web.
  /// `billing`/`shipping` sunt map-uri cu chei snake_case (`postal_code`),
  /// exact ce validează `CheckoutController::addressRules` — spre deosebire
  /// de comanda întoarsă, unde adresele au `postalCode` camelCase (vezi
  /// `models/order.dart`).
  Future<CheckoutResult> checkout({
    required Map<String, String> billing,
    required Map<String, String> shipping,
    required String shippingCode,
    required String paymentCode,
  }) async {
    final response = await _client.post(
      _uri('checkout'),
      headers: await _headers(auth: true),
      body: jsonEncode({
        'billing': billing,
        'shipping': shipping,
        'shippingCode': shippingCode,
        'paymentCode': paymentCode,
      }),
    );
    final body = _decodeMap(response);
    final rawPayment = body['payment'] as Map<String, dynamic>?;

    return CheckoutResult(
      order: OrderDetail.fromJson(body['data'] as Map<String, dynamic>),
      payment: rawPayment != null ? PaymentRedirect.fromJson(rawPayment) : null,
    );
  }

  /// `GET /api/v1/orders` — comenzile utilizatorului curent, cele mai noi
  /// primele (`OrderController::index`, scoped pe `user_id`).
  Future<List<OrderSummary>> orders() async {
    final response = await _client.get(_uri('orders'), headers: await _headers(auth: true));
    final data = _decodeMap(response)['data'] as List<dynamic>? ?? const [];

    return data.map((item) => OrderSummary.fromJson(item as Map<String, dynamic>)).toList();
  }

  /// `GET /api/v1/orders/{number}` — detaliul unei comenzi proprii; o comandă
  /// a altcuiva întoarce 404, nu doar 403 (numerele nu sunt enumerabile).
  Future<OrderDetail> orderByNumber(String number) async {
    final response = await _client.get(_uri('orders/$number'), headers: await _headers(auth: true));

    return OrderDetail.fromJson(_decodeMap(response)['data'] as Map<String, dynamic>);
  }

  /// Decodează corpul JSON al unui răspuns și îl mapează la o [ApiException]
  /// dacă statusul nu e de succes — 401 declanșează [onUnauthorized] (dacă e
  /// setat), 422 devine `fieldErrors`, restul rămâne un mesaj simplu.
  Map<String, dynamic> _decodeMap(http.Response response) {
    final decoded = response.body.isEmpty ? <String, dynamic>{} : jsonDecode(response.body);
    final map = decoded is Map<String, dynamic> ? decoded : <String, dynamic>{};

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return map;
    }

    if (response.statusCode == 401) {
      onUnauthorized?.call();
    }

    if (response.statusCode == 422) {
      final rawErrors = map['errors'] as Map<String, dynamic>? ?? const {};
      final fieldErrors = rawErrors.map(
        (field, messages) => MapEntry(field, (messages as List<dynamic>).cast<String>()),
      );

      throw ApiException(
        map['message'] as String? ?? 'Datele trimise nu sunt valide.',
        statusCode: response.statusCode,
        fieldErrors: fieldErrors,
      );
    }

    throw ApiException(
      map['message'] as String? ?? 'A apărut o eroare neașteptată (${response.statusCode}).',
      statusCode: response.statusCode,
    );
  }
}
