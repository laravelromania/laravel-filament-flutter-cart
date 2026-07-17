import 'dart:convert';

import 'package:http/http.dart' as http;

import 'config.dart';
import 'models/category.dart';
import 'models/product.dart';
import 'models/product_detail.dart';

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
/// Partea 13 acoperă doar catalogul, care e public (fără token). Partea 14
/// adaugă autentificarea: [_headers] va atașa acolo unde `auth: true`
/// antetul `Authorization: Bearer <token>` citit din `flutter_secure_storage`,
/// iar lângă metodele de mai jos se adaugă register/login/logout/me, cartul
/// și checkout-ul — fără să schimbe forma acestei clase.
class ApiService {
  ApiService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Uri _uri(String path, [Map<String, dynamic>? query]) {
    final normalizedQuery = query == null
        ? null
        : query.map((key, value) => MapEntry(key, value.toString()));

    return Uri.parse('${Config.baseUrl}${Config.apiPrefix}/$path')
        .replace(queryParameters: normalizedQuery);
  }

  /// `auth` nu are efect încă (rămâne `false` peste tot în Partea 13) — e
  /// seamul pe care Partea 14 îl folosește pentru a atașa tokenul Sanctum.
  Map<String, String> _headers({bool auth = false}) {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
  }

  /// `GET /api/v1/products` — listare paginată, cu filtre opționale
  /// (`search`, `brand`, `category`, `price_min`, `price_max`, `sort`,
  /// `per_page`). Returnează doar pagina curentă de produse; ecranul de
  /// listare din Partea 13 afișează prima pagină (paginarea completă e lăsată
  /// ca temă pentru cititor).
  Future<List<Product>> products({Map<String, dynamic>? filters}) async {
    final response = await _client.get(_uri('products', filters), headers: _headers());
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
    final response = await _client.get(_uri('products/$slug'), headers: _headers());
    final body = _decodeMap(response);

    return ProductDetail.fromJson(body['data'] as Map<String, dynamic>);
  }

  /// `GET /api/v1/categories` — arborele complet de categorii active.
  Future<List<Category>> categories() async {
    final response = await _client.get(_uri('categories'), headers: _headers());
    final body = _decodeMap(response);
    final data = body['data'] as List<dynamic>? ?? const [];

    return data.map((item) => Category.fromJson(item as Map<String, dynamic>)).toList();
  }

  /// Decodează corpul JSON al unui răspuns și îl mapează la o [ApiException]
  /// dacă statusul nu e de succes — 422 devine `fieldErrors`, restul un mesaj
  /// simplu (401 va fi tratat de Partea 14 ca semnal de deconectare forțată).
  Map<String, dynamic> _decodeMap(http.Response response) {
    final decoded = response.body.isEmpty ? <String, dynamic>{} : jsonDecode(response.body);
    final map = decoded is Map<String, dynamic> ? decoded : <String, dynamic>{};

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return map;
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
