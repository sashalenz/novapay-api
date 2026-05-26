<?php

namespace Sashalenz\NovapayApi\RequestData\Auth;

use Spatie\LaravelData\Data;

/**
 * Second-factor authentication (OTP).
 * Will be deprecated on 31.08.2026.
 */
class UserAuthRequest extends Data
{
    public function __construct(
        public string $temp_principal,
        public int $code_operation_otp,
        public ?string $otp_password = null,
        public ?string $request_ref = null,
    ) {}
}
