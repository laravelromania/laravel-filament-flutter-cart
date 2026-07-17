/// Cumpărătorul autentificat, exact cum îl serializează `UserResource`
/// (Partea 12): id, nume, email — niciodată hash-ul parolei sau vreun token.
class AppUser {
  final int id;
  final String name;
  final String email;

  const AppUser({required this.id, required this.name, required this.email});

  factory AppUser.fromJson(Map<String, dynamic> json) {
    return AppUser(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
    );
  }
}

/// Perechea token + utilizator întoarsă de `POST /register` și `POST /login`
/// (Partea 12) — un obiect dedicat, nu un `Map` anonim, ca [AuthProvider] să
/// nu tot despacheteze chei brute de JSON.
class AuthResult {
  final String token;
  final AppUser user;

  const AuthResult({required this.token, required this.user});
}
