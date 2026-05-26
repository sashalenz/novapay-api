<?php

namespace Sashalenz\NovapayApi\ResponseData\Auth;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

/**
 * Response from RefreshUserAuthentication (deprecated 31.08.2026).
 */
class RefreshAuthResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        public string $result,
        public ?string $new_principal,
        public ?string $expiration,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
