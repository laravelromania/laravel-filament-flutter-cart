import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Singurul loc care știe cheia de storage pentru tokenul Sanctum. Atât
/// [ApiService] (care doar CITEȘTE tokenul, ca să-l atașeze pe cererile
/// autentificate) cât și `AuthProvider` (care SCRIE/ȘTERGE tokenul la
/// login/register/logout) trec prin acest wrapper static, ca cele două să nu
/// poată ajunge vreodată să folosească chei diferite.
///
/// De ce `flutter_secure_storage` și nu `shared_preferences`: al doilea scrie
/// direct pe disc, necriptat (un fișier XML pe Android, un plist pe iOS) — ok
/// pentru preferințe fără miză, dar un token Sanctum e efectiv o parolă pe
/// termen lung pentru contul utilizatorului (rămâne valid până la logout sau
/// revocare manuală). `flutter_secure_storage` îl ține în Keystore-ul criptat
/// al Android-ului, respectiv în Keychain pe iOS — protecția la care te-ai
/// aștepta de la un client care ține o parolă. Detalii în articolul acestei
/// părți din serie.
class TokenStorage {
  TokenStorage._();

  static const FlutterSecureStorage _storage = FlutterSecureStorage();
  static const String _key = 'auth_token';

  /// `null` dacă nimeni nu s-a autentificat încă (sau după un `delete`).
  static Future<String?> read() => _storage.read(key: _key);

  static Future<void> write(String token) => _storage.write(key: _key, value: token);

  static Future<void> delete() => _storage.delete(key: _key);
}
