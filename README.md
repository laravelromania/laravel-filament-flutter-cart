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
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build          # compilează Tailwind 4 + assets storefront
php artisan serve
```

- Storefront: `http://127.0.0.1:8000/`
- Panou admin (Filament): `http://127.0.0.1:8000/admin`

Creează un utilizator de admin pentru panou:

```bash
php artisan make:filament-user
```

(La bootstrap există deja un cont dev: `admin@shop.test` / `password` — vezi
`backend/VERSIONS.md`. NU folosi aceste credențiale în producție.)

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
