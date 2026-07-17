<?php

return [
    'name' => 'Shipping',

    /*
    |--------------------------------------------------------------------------
    | Generic drivers
    |--------------------------------------------------------------------------
    | Always available, no courier account required. Prices are in bani (minor
    | units), matching Core\ValueObjects\Money.
    */

    'flat' => [
        'label' => env('SHIPPING_FLAT_LABEL', 'Livrare standard (tarif fix)'),
        'amount' => (int) env('SHIPPING_FLAT_AMOUNT', 1999),
    ],

    'weight' => [
        'label' => env('SHIPPING_WEIGHT_LABEL', 'Livrare în funcție de greutate'),
        // maxKg => bani. First tier whose bound is not exceeded wins.
        'tiers' => [
            1 => 1500,
            5 => 2500,
            30 => 4000,
        ],
        'fallback' => 6000,
    ],

    'zone' => [
        'label' => env('SHIPPING_ZONE_LABEL', 'Livrare pe zone'),
        'default_zone' => 'national',
        // zone => bani
        'rates' => [
            'local' => 1499,
            'national' => 1999,
            'remote' => 2999,
        ],
        // county => zone (unlisted counties fall back to default_zone)
        'counties' => [
            'București' => 'local',
            'Ilfov' => 'local',
            'Cluj' => 'national',
            'Timiș' => 'national',
            'Iași' => 'national',
            'Brașov' => 'national',
            'Constanța' => 'national',
            'Tulcea' => 'remote',
            'Caraș-Severin' => 'remote',
            'Bistrița-Năsăud' => 'remote',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Romanian couriers
    |--------------------------------------------------------------------------
    | `sandbox` defaults to true. The drivers also force sandbox on whenever the
    | credentials are missing, so you can never accidentally hit a live courier
    | API without a real account. Set the *_SANDBOX vars to false + fill in the
    | credentials to go live.
    */

    'sameday' => [
        'label' => env('SHIPPING_SAMEDAY_LABEL', 'Sameday (curier)'),
        'username' => env('SHIPPING_SAMEDAY_USERNAME', ''),
        'password' => env('SHIPPING_SAMEDAY_PASSWORD', ''),
        'sandbox' => filter_var(env('SHIPPING_SAMEDAY_SANDBOX', true), FILTER_VALIDATE_BOOL),
        'base_url' => env('SHIPPING_SAMEDAY_BASE_URL', 'https://sameday-api.demo.zitec.com'),
    ],

    'cargus' => [
        'label' => env('SHIPPING_CARGUS_LABEL', 'Cargus (curier)'),
        'subscription_key' => env('SHIPPING_CARGUS_SUBSCRIPTION_KEY', ''),
        'username' => env('SHIPPING_CARGUS_USERNAME', ''),
        'password' => env('SHIPPING_CARGUS_PASSWORD', ''),
        'sandbox' => filter_var(env('SHIPPING_CARGUS_SANDBOX', true), FILTER_VALIDATE_BOOL),
        'base_url' => env('SHIPPING_CARGUS_BASE_URL', 'https://urgentcargus.azure-api.net/api'),
    ],
];
