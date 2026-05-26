<?php

namespace Sashalenz\NovapayApi\RequestData\Auth;

use Spatie\LaravelData\Data;

/**
 * Refresh session token (principal).
 * Will be deprecated on 31.08.2026.
 */
class RefreshAuthRequest extends Data
{
    public function __construct(
        public string $principal,
        public ?string $request_ref = null,
    ) {}
}
