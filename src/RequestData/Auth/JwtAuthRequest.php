<?php

namespace Sashalenz\NovapayApi\RequestData\Auth;

use Spatie\LaravelData\Data;

class JwtAuthRequest extends Data
{
    public function __construct(
        public string $refresh_token,
        public string $login,
        public string $public_certificate,
        public ?string $request_ref = null,
    ) {}
}
