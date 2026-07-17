/// Configurare simplă pentru mediul de dezvoltare local.
///
/// Backend-ul Laravel trebuie pornit ascultând pe toate interfețele, nu doar
/// pe loopback, altfel emulatorul/dispozitivul nu îl poate contacta:
///
/// ```bash
/// php artisan serve --host=0.0.0.0
/// ```
///
/// Iar `baseUrl` de mai jos depinde de ținta pe care rulează aplicația:
///
/// - Emulator Android: `http://10.0.2.2:8000` — `10.0.2.2` e alias-ul special
///   pe care emulatorul îl expune pentru `localhost`-ul mașinii gazdă (NU
///   `127.0.0.1` și NU `localhost` — acelea ar trimite către emulatorul
///   însuși, nu către calculatorul pe care rulează `php artisan serve`).
/// - Simulator iOS: `http://127.0.0.1:8000` — simulatorul rulează direct pe
///   mașina gazdă, deci loopback-ul obișnuit funcționează.
/// - Dispozitiv fizic (telefon real, Android sau iOS): niciuna dintre cele de
///   mai sus nu merge — ai nevoie de adresa IP din rețeaua locală a
///   calculatorului (ex. `http://192.168.1.20:8000`), iar telefonul trebuie
///   să fie pe același Wi-Fi.
///
/// Schimbă manual constanta de mai jos în funcție de unde rulezi aplicația.
class Config {
  Config._();

  /// Vezi nota de mai sus — implicit setat pentru emulatorul Android.
  static const String baseUrl = 'http://10.0.2.2:8000';

  /// Toate endpoint-urile backend-ului trăiesc sub acest prefix (vezi Partea 12).
  static const String apiPrefix = '/api/v1';
}
