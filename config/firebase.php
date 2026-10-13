<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account credentials file used by the
    | Firebase Admin SDK to authenticate server-side requests.
    |
    */

    'credentials' => env('FIREBASE_CREDENTIALS'),

    /*
    |--------------------------------------------------------------------------
    | Firebase CA Certificate Bundle
    |--------------------------------------------------------------------------
    |
    | Optional path to a CA certificate bundle used by Guzzle when verifying
    | TLS connections to the Firebase APIs. This is required on Windows where
    | PHP/OpenSSL has no curl.cainfo / openssl.cafile configured.
    |
    */

    'ca_cert' => env('FIREBASE_CA_CERT'),

];