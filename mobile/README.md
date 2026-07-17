# Magazin modular — aplicația Flutter

Sursele aplicației mobile pentru seria [„Magazin online modular"](https://laravel.ro/articole/magazin-modular-laravel-01-arhitectura)
de pe [laravel.ro](https://laravel.ro). Acest folder conține **doar** `lib/*.dart`
și `pubspec.yaml` — nu și proiectul Flutter complet (`android/`, `ios/` etc.),
pentru că SDK-ul Flutter nu rulează pe mașina pe care s-a scris codul din
acest repo.

## Rulare locală

Ai nevoie de [Flutter SDK](https://docs.flutter.dev/get-started/install)
instalat local (stabil, canal `stable`).

```bash
cd mobile
flutter create .        # generează android/ ios/ etc. în jurul surselor existente
flutter pub get
flutter run
```

`flutter create .` nu suprascrie fișierele din `lib/` sau `pubspec.yaml` — doar
adaugă folderele de platformă lipsă (`android/`, `ios/`, `linux/`, `macos/`,
`windows/`, `web/`), pe care `.gitignore`-ul repo-ului le exclude oricum.

## Backend-ul și `baseUrl`

Aplicația consumă API-ul Laravel din `backend/` (`/api/v1`, vezi
[Partea 12](https://laravel.ro/articole/magazin-modular-laravel-12-api-mobil)).
Pornește-l ascultând pe toate interfețele, nu doar pe loopback:

```bash
cd backend
php artisan serve --host=0.0.0.0
```

`lib/config.dart` fixează `baseUrl`-ul folosit de `ApiService`. Valoarea
corectă depinde de unde rulează aplicația:

- **Emulator Android** — `http://10.0.2.2:8000`. `10.0.2.2` e alias-ul special
  pe care emulatorul îl expune pentru `localhost`-ul mașinii gazdă; nici
  `127.0.0.1`, nici `localhost` nu funcționează aici (ar trimite cererea către
  emulatorul însuși).
- **Simulator iOS** — `http://127.0.0.1:8000` (simulatorul rulează pe aceeași
  mașină, deci loopback-ul obișnuit e suficient).
- **Dispozitiv fizic** (Android sau iOS) — adresa IP din rețeaua locală a
  calculatorului (ex. `http://192.168.1.20:8000`), cu telefonul pe același Wi-Fi.

Schimbă manual constanta din `lib/config.dart` pentru ținta pe care testezi.

## Ce conține Partea 13

- `lib/config.dart` — `baseUrl` + gotcha-ul de mai sus.
- `lib/api_service.dart` — client `http` subțire peste `/api/v1`: `products()`,
  `productBySlug()`, `categories()`. Catalogul e public, deci niciun apel din
  Partea 13 nu are nevoie de token.
- `lib/models/` — `Money`, `Product`, `ProductDetail` (+ `ProductVariant`,
  `VariantAttribute`, `ProductCategoryRef`), `Category`, fiecare cu `fromJson`
  potrivit exact pe JSON-ul întors de `Modules/Api` (Partea 12).
- `lib/screens/product_list_screen.dart` — catalogul, sub formă de grid, cu
  `FutureBuilder` + `RefreshIndicator`.
- `lib/screens/product_detail_screen.dart` — detaliul unui produs: galerie,
  selector de variantă, preț (`Money.formatted`) și un buton „Adaugă în coș"
  încă inert (se cablează în Partea 14).
- `lib/main.dart` — `MaterialApp` cu `ProductListScreen` ca ecran de start.

Partea 14 adaugă autentificarea (`flutter_secure_storage` + `provider`), coșul
și checkout-ul, peste exact aceleași fișiere.

## Articole

- [Partea 12 — Modulul Api](https://laravel.ro/articole/magazin-modular-laravel-12-api-mobil) (backend-ul consumat aici)
- [Partea 13 — Aplicația Flutter: setup și catalog](https://laravel.ro/articole/magazin-modular-laravel-13-flutter-catalog) (acest folder)
- [Partea 14 — Coșul și checkout-ul din Flutter](https://laravel.ro/articole/magazin-modular-laravel-14-flutter-cos-checkout)
- Vezi și seria dedicată [Flutter + Laravel](https://laravel.ro/articole/flutter-laravel-app-login) pentru autentificare Sanctum de la zero.
