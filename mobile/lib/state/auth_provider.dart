import 'package:flutter/foundation.dart';

import '../api_service.dart';
import '../models/user.dart';
import 'token_storage.dart';

/// Starea de autentificare a aplicației — cine e logat (dacă e cineva) și
/// tokenul care dovedește asta. Singura sursă de adevăr pentru `isAuthed`;
/// ecranele citesc de aici prin `context.watch<AuthProvider>()`, niciodată
/// direct din [TokenStorage].
///
/// Tokenul persistă în `flutter_secure_storage` (vezi `token_storage.dart`),
/// deci supraviețuiește restart-ului aplicației — [bootstrap] îl reîncarcă la
/// pornire, înainte ca ecranele care cer autentificare să decidă ce arată.
class AuthProvider extends ChangeNotifier {
  AuthProvider({required ApiService api}) : _api = api;

  final ApiService _api;

  String? _token;
  AppUser? _user;
  bool _bootstrapping = true;
  String? _error;

  bool get isAuthed => _token != null;
  AppUser? get user => _user;

  /// `true` cât timp [bootstrap] încă citește tokenul salvat / reîmprospătează
  /// profilul — ecranele care cer autentificare arată un spinner în loc să
  /// afișeze fals „trebuie să te loghezi" pentru o clipă, la fiecare pornire.
  bool get bootstrapping => _bootstrapping;

  /// Mesajul ultimei erori de `register`/`login` (primul mesaj de validare
  /// 422, dacă există unul, altfel mesajul generic al `ApiException`).
  String? get error => _error;

  /// Citește tokenul salvat (dacă există) la pornirea aplicației și
  /// reîmprospătează profilul din `GET /user`. Dacă tokenul nu mai e valid
  /// (revocat manual, expirat), `GET /user` întoarce 401 — [ApiService]
  /// apelează atunci [onUnauthorized] (cablat spre [forceLogout] în
  /// `main.dart`), deci ajungem tot la o stare curată, fără duplicarea logicii
  /// de curățare aici.
  Future<void> bootstrap() async {
    _token = await TokenStorage.read();

    if (_token != null) {
      try {
        _user = await _api.me();
      } on ApiException {
        await forceLogout();
      }
    }

    _bootstrapping = false;
    notifyListeners();
  }

  /// `POST /register` — creează contul, primește un token, îl salvează.
  /// Întoarce `true` la succes; la eșec, [error] descrie ce a mers prost și
  /// metoda întoarce `false` (ecranul de înregistrare arată mesajul).
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) {
    return _authenticate(() => _api.register(
          name: name,
          email: email,
          password: password,
          passwordConfirmation: passwordConfirmation,
        ));
  }

  /// `POST /login` — la fel ca [register], dar cu credențiale existente.
  Future<bool> login({required String email, required String password}) {
    return _authenticate(() => _api.login(email: email, password: password));
  }

  Future<bool> _authenticate(Future<AuthResult> Function() call) async {
    _error = null;

    try {
      final result = await call();
      await TokenStorage.write(result.token);
      _token = result.token;
      _user = result.user;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = _firstMessage(e);
      notifyListeners();
      return false;
    }
  }

  /// Deconectare „normală": revocă tokenul pe server (`POST /logout`), apoi
  /// curăță starea locală indiferent dacă apelul de rețea a reușit — un
  /// token deja invalid tot trebuie șters din storage.
  Future<void> logout() async {
    try {
      await _api.logout();
    } on ApiException {
      // Ignorăm — tokenul e oricum pe cale să dispară local mai jos.
    }

    await forceLogout();
  }

  /// Deconectare „locală", fără niciun apel de rețea — folosită de
  /// [ApiService.onUnauthorized] când un 401 dovedește că tokenul salvat nu
  /// mai e valid, ca să nu mai încercăm un `/logout` care ar eșua la fel.
  Future<void> forceLogout() async {
    await TokenStorage.delete();
    _token = null;
    _user = null;
    notifyListeners();
  }

  String _firstMessage(ApiException e) {
    final fieldMessages = e.fieldErrors?.values;
    if (fieldMessages != null && fieldMessages.isNotEmpty && fieldMessages.first.isNotEmpty) {
      return fieldMessages.first.first;
    }

    return e.message;
  }
}
