<?php

namespace Sashalenz\NovapayApi\ResponseData\Auth;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class JwtAuthResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        // Success-only fields: absent on error responses (which carry only
        // request_ref/response_ref/error), so they must be optional — otherwise
        // spatie-data throws CannotCreateData before isSuccessful() can run,
        // masking the real API error.
        public ?string $jwt = null,
        public ?string $expiration = null,
        public ?string $refresh_token = null,
        public ?string $public_certificate = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->error === null;
    }
}
