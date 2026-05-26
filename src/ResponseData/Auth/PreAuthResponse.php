<?php

namespace Sashalenz\NovapayApi\ResponseData\Auth;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

/**
 * Response from PreUserAuthentication (first-factor, deprecated 31.08.2026).
 */
class PreAuthResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        public string $result,
        public ?string $user_id,          // keep as string; cast to int where needed
        public ?string $temp_principal,
        public ?string $code_operation_otp, // keep as string; cast to int where needed
        public ?string $expiration,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }

    public function getUserId(): ?int
    {
        return $this->user_id !== null ? (int) $this->user_id : null;
    }

    public function getCodeOperationOtp(): ?int
    {
        return $this->code_operation_otp !== null ? (int) $this->code_operation_otp : null;
    }
}
