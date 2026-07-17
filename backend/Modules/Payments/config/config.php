<?php

return [
    'name' => 'Payments',

    /*
    |--------------------------------------------------------------------------
    | Payment gateways
    |--------------------------------------------------------------------------
    | Both drivers default to SANDBOX. A driver also FORCES sandbox on whenever
    | its credentials are missing, so you can never accidentally hit a live
    | processor without a real merchant account. Flip *_SANDBOX to false and fill
    | in the real keys to go live.
    |
    | `sandbox_secret` is the key the built-in "simulează plata" screen signs its
    | callbacks with (an HMAC), so the signature-verification path is exercised
    | end-to-end even without a merchant account. In production the real signature
    | is Netopia's RSA envelope / PayU's HMAC — see each driver's verifySignature().
    */

    'sandbox_secret' => env('PAYMENTS_SANDBOX_SECRET', 'sandbox-secret-key'),

    'netopia' => [
        'label' => env('PAYMENTS_NETOPIA_LABEL', 'Card bancar (Netopia / mobilPay)'),
        // Netopia mobilPay: your merchant signature + the RSA key pair used to
        // encrypt the request and verify the IPN.
        'signature' => env('PAYMENTS_NETOPIA_SIGNATURE', ''),
        'public_cert' => env('PAYMENTS_NETOPIA_PUBLIC_CERT', ''),
        'private_key' => env('PAYMENTS_NETOPIA_PRIVATE_KEY', ''),
        'sandbox' => filter_var(env('PAYMENTS_NETOPIA_SANDBOX', true), FILTER_VALIDATE_BOOL),
        'base_url' => env('PAYMENTS_NETOPIA_BASE_URL', 'https://sandboxsecure.mobilpay.ro'),
    ],

    'payu' => [
        'label' => env('PAYMENTS_PAYU_LABEL', 'Card bancar (PayU)'),
        // PayU: the POS credentials + the secret key used both for the OAuth flow
        // and to sign / verify the IPN notification hash.
        'merchant' => env('PAYMENTS_PAYU_MERCHANT', ''),
        'pos_id' => env('PAYMENTS_PAYU_POS_ID', ''),
        'secret' => env('PAYMENTS_PAYU_SECRET', ''),
        'sandbox' => filter_var(env('PAYMENTS_PAYU_SANDBOX', true), FILTER_VALIDATE_BOOL),
        'base_url' => env('PAYMENTS_PAYU_BASE_URL', 'https://secure.snd.payu.com'),
    ],
];
