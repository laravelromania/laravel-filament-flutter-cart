# Magazin online modular — Laravel + Filament + Flutter

Cod companion pentru seria-tutorial de pe [laravel.ro](https://laravel.ro):
construim pas-cu-pas un **magazin online modular**, cu back-office în Filament 4,
storefront în Livewire 3 + Tailwind 4, API JSON cu Sanctum și un client mobil
Flutter. Arhitectura e împărțită în module independente (`nwidart/laravel-modules`):
Core, Catalog, Cart, Customers, Checkout, Orders, Shipping, Payments, Api.

Fiecare parte a seriei are un **tag git** (`part-00` … `part-15`) — poți face
checkout la orice tag ca să vezi codul exact de la finalul acelei părți.

## Structura repo-ului

```
laravel-filament-flutter-cart/
├── backend/          # Laravel 13 + Filament 4 + nwidart/laravel-modules (SQLite)
│   ├── app/ config/ routes/ database/ ...   # scaffold Laravel standard
│   ├── Modules/                             # modulele magazinului (se umplu pe parcurs)
│   └── VERSIONS.md                          # versiunile exacte ale dependențelor
└── mobile/           # surse Flutter (doar lib/ + pubspec.yaml) — de la partea 13
```

## Backend (Laravel + Filament)

Cerințe: PHP 8.3+ (testat pe 8.4), Composer, Node 20+ / npm.

```bash
cd backend
COMPOSER_ALLOW_SUPERUSER=1 composer install   # flag-ul e necesar doar dacă rulezi ca root
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # DB_CONNECTION=sqlite e deja setat în .env.example
php artisan migrate --seed       # rulează și seederele: brands/categorii/produse demo + admin
npm install
npm run build          # compilează Tailwind 4 + assets storefront
php artisan serve
```

- Storefront: `http://127.0.0.1:8000/`
- Panou admin (Filament): `http://127.0.0.1:8000/admin`

`migrate --seed` creează automat contul de admin dev: `admin@shop.test` /
`password` (vezi `Modules/Core/database/seeders/AdminUserSeeder.php`), plus un
catalog demo (categorii, branduri, produse cu variante) și un client demo, ca
storefront-ul și panoul să nu pornească goale. NU folosi aceste credențiale
în producție — vezi și `backend/VERSIONS.md` pentru versiunile exacte ale
tuturor dependențelor.

Versiunile exacte ale tuturor dependențelor (Laravel, Filament, Livewire,
nwidart, Sanctum, spatie, Tailwind, Vite) sunt în [`backend/VERSIONS.md`](backend/VERSIONS.md).

## Mobile (Flutter)

Folderul `mobile/` conține **doar sursele** (`lib/*.dart` + `pubspec.yaml`);
proiectul Flutter propriu-zis se generează local, pentru că SDK-ul Flutter nu e
inclus în repo:

```bash
cd mobile
flutter create .          # generează android/ ios/ etc. în jurul surselor
flutter pub get
flutter run
```

Notă emulator Android: `baseUrl`-ul API folosește `http://10.0.2.2:8000` pentru
a ajunge la `localhost` de pe mașina gazdă; pornește backend-ul cu
`php artisan serve --host=0.0.0.0`.

## Tag-uri ↔ articole

Fiecare parte publicată are un tag și un articol corespondent. Tabelul se
completează pe măsură ce apar părțile:

| Tag | Parte | Articol |
|-----|-------|---------|
| `part-00` | Bootstrap (tooling + schelet repo) | — |
| `part-01` | Arhitectura modulară & setup | https://laravel.ro/articole/magazin-modular-laravel-01-arhitectura |
| `part-02` | Modulul Core | https://laravel.ro/articole/magazin-modular-laravel-02-core |
| `part-03` | Catalog I: modele & admin | https://laravel.ro/articole/magazin-modular-laravel-03-catalog-admin |
| `part-04` | Catalog II: variante & stoc | https://laravel.ro/articole/magazin-modular-laravel-04-catalog-variante |
| `part-05` | Storefront catalog | https://laravel.ro/articole/magazin-modular-laravel-05-storefront-catalog |
| `part-06` | Modulul Cart | https://laravel.ro/articole/magazin-modular-laravel-06-cos |
| `part-07` | Clienți & autentificare | https://laravel.ro/articole/magazin-modular-laravel-07-clienti-autentificare |
| `part-08` | Checkout | https://laravel.ro/articole/magazin-modular-laravel-08-checkout |
| `part-09` | Modulul Orders | https://laravel.ro/articole/magazin-modular-laravel-09-comenzi |
| `part-10` | Modulul Shipping | https://laravel.ro/articole/magazin-modular-laravel-10-shipping |
| `part-11` | Modulul Payments | https://laravel.ro/articole/magazin-modular-laravel-11-payments |
| `part-12` | API mobil (Sanctum) | https://laravel.ro/articole/magazin-modular-laravel-12-api-mobil |
| `part-13` | Flutter I: setup & catalog | https://laravel.ro/articole/magazin-modular-laravel-13-flutter-catalog |
| `part-14` | Flutter II: coș & checkout | https://laravel.ro/articole/magazin-modular-laravel-14-flutter-cos-checkout |
| `part-15` | Testare, seed & deploy | https://laravel.ro/articole/magazin-modular-laravel-15-testare-deploy |

## Note

- Driverele de curier (Sameday/Cargus) și de plată (Netopia/PayU) rulează în
  **mod sandbox/mock** — codul e complet, dar are nevoie de conturi reale și de
  chei în `.env` pentru a funcționa cu serviciile live.
- Baza de date implicită e **SQLite** (zero config), potrivită pentru rulat local.
- Suita de teste (Pest) e verde end-to-end: `php artisan test` din `backend/`.

## Licență & credite

Cod companion educațional pentru seria de articole de pe
[laravel.ro](https://laravel.ro) — liber de folosit, adaptat sau extins pentru
învățare, teste proprii sau prototipuri (fără nicio garanție; NU e pregătit
"ca atare" pentru producție — vezi notele de sandbox de mai sus). Construit cu
[Laravel](https://laravel.com), [Filament](https://filamentphp.com),
[Livewire](https://livewire.laravel.com), [nwidart/laravel-modules](https://nwidart.com/laravel-modules/)
și [Flutter](https://flutter.dev) — toate sub licențele lor originale (în
majoritate MIT).
