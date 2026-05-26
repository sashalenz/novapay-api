<?php

return [
    /*
     * NovaPay Business Cabinet API endpoint.
     */
    'api_url' => env('NOVAPAY_API_URL', 'https://business.novapay.ua/Services/ClientAPIService.svc'),

    /*
     * Login (username) for authentication.
     */
    'login' => env('NOVAPAY_LOGIN'),

    /*
     * Refresh Token generated in Business Cabinet → System → Settings → API.
     */
    'refresh_token' => env('NOVAPAY_REFRESH_TOKEN'),

    /*
     * RSA public certificate (PEM format) generated in Business Cabinet.
     */
    'public_certificate' => env('NOVAPAY_PUBLIC_CERTIFICATE'),

    /*
     * HTTP timeout in seconds.
     */
    'timeout' => env('NOVAPAY_TIMEOUT', 30),
];
