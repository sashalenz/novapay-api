<?php

namespace Sashalenz\NovapayApi\ResponseData\Auth;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class JwtAuthResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        public string $jwt,
        public string $expiration,
        public string $refresh_token,
        public string $public_certificate,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->error === null;
    }
}
