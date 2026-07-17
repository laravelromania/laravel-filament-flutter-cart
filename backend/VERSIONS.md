# Versiuni exacte (bootstrap Task 0 / `part-00`)

Snapshot al versiunilor rezolvate la crearea scheletului. Articolele seriei
citează aceste versiuni; nu le modifica retroactiv fără să actualizezi și textul.

Generat: 2026-07-17 · PHP 8.4.20 · Composer 2.9.2 · Node v22 · npm 10

## Platformă

| Component | Versiune |
|-----------|----------|
| PHP | 8.4.20 |
| Composer | 2.9.2 |
| Node | v22 |
| npm | 10 |
| Laravel (`laravel/framework`) | v13.20.0 |

## Dependențe Composer (backend)

| Pachet | Versiune rezolvată | Constrângere cerută |
|--------|--------------------|---------------------|
| `filament/filament` | v4.11.8 | `^4.0` |
| `filament/support` | v4.11.8 | (tras de filament) |
| `livewire/livewire` | v3.8.2 | (vine cu Filament) |
| `nwidart/laravel-modules` | v13.0.0 | `^13.0` |
| `laravel/sanctum` | v4.3.2 | via `php artisan install:api` |
| `spatie/laravel-permission` | v8.3.0 | `^8.3` |
| `spatie/laravel-medialibrary` | v11.23.2 | `^11.23` |

## Dependențe npm (storefront / Vite)

Tailwind 4 și pluginul Vite vin deja preconfigurate în scheletul Laravel 13
(`vite.config.js` are `@tailwindcss/vite`, iar `resources/css/app.css` are
`@import 'tailwindcss'`). Doar am rulat `npm install` + `npm run build`.

| Pachet | Versiune rezolvată |
|--------|--------------------|
| `tailwindcss` | 4.3.3 |
| `@tailwindcss/vite` | 4.3.3 |
| `vite` | 8.1.5 |
| `laravel-vite-plugin` | 3.1.3 |

## Panou Filament

- `php artisan filament:install --panels` → panel id **`admin`**, path **`/admin`**.
- Provider: `app/Providers/Filament/AdminPanelProvider.php` (înregistrat în
  `bootstrap/providers.php`).
- Descoperire resurse: `app_path('Filament/Resources')` (mutată per-modul în
  Task 1).

### Utilizator admin (DOAR dev — a nu se folosi în producție)

Creat cu `php artisan make:filament-user`:

- Email: `admin@shop.test`
- Parolă: `password`

## Structura de foldere nwidart (`php artisan module:make Demo`)

Ruleaza `php artisan module:make <Nume>` generează (observat pe v13.0.0):

```
Modules/<Nume>/
├── module.json                     # meta modul (name, alias, providers, priority)
├── composer.json                   # PSR-4: Modules\<Nume>\  ->  app/  (merge-plugin)
├── package.json
├── vite.config.js
├── app/
│   ├── Http/Controllers/<Nume>Controller.php
│   └── Providers/
│       ├── <Nume>ServiceProvider.php
│       ├── EventServiceProvider.php
│       └── RouteServiceProvider.php
├── config/config.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/<Nume>DatabaseSeeder.php
├── resources/
│   ├── assets/{js/app.js, sass/app.scss}
│   └── views/{index.blade.php, components/layouts/master.blade.php}
├── routes/{web.php, api.php}
└── tests/{Feature/, Unit/}
```

Note cheie:
- Namespace implicit: `Modules` (vezi `config/modules.php` → `'namespace' => 'Modules'`).
  Codul PHP al unui modul stă în `Modules/<Nume>/app/` mapat la `Modules\<Nume>\`.
  Ex: provider = `Modules\<Nume>\Providers\<Nume>ServiceProvider`.
- Generatoare active by default (`generate => true`): provider, route-provider,
  controller, config, factory, migration, seeder. Restul (Models, Events,
  Listeners, Casts, Enums, Services, Repositories, Policies, Rules etc.) au
  `generate => false` — se creează la cerere prin `module:make-*` sau manual.
- Căi utile (din `config/modules.php`):
  - Modele: `Modules/<N>/app/Models/`
  - Events / Listeners: `Modules/<N>/app/Events/`, `.../app/Listeners/`
  - Migrări: `Modules/<N>/database/migrations/`
  - Rute: `Modules/<N>/routes/{web,api}.php`
  - Config: `Modules/<N>/config/config.php`
- Stare module: `modules_statuses.json` în rădăcina backend-ului
  (`{"<Nume>": true/false}`).
- `module:make` NU rulează `composer dump-autoload`; după creare rulează
  `composer dump-autoload` ca providerul modulului să fie autoload-abil
  (altfel boot-ul cade cu „Class ...ServiceProvider not found").

## Note de mediu (non-interactive shell)

- Pluginurile Composer sunt dezactivate implicit → toate comenzile composer/artisan
  care declanșează scripturi au fost rulate cu `COMPOSER_ALLOW_SUPERUSER=1`.
- `nwidart/laravel-modules` trage `wikimedia/composer-merge-plugin`; a trebuit
  permis explicit:
  `composer config allow-plugins.wikimedia/composer-merge-plugin true`.
