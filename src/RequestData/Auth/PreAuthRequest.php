<?php

namespace Sashalenz\NovapayApi\RequestData\Auth;

use Spatie\LaravelData\Data;

/**
 * First-factor authentication (login + password).
 * Will be deprecated on 31.08.2026.
 */
class PreAuthRequest extends Data
{
    public function __construct(
        public string $login,
        public string $password,
        public ?string $phone = null,
        public ?string $request_ref = null,
    ) {}
}
