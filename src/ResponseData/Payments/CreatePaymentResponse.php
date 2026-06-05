<?php

namespace Sashalenz\NovapayApi\ResponseData\Payments;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class CreatePaymentResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        // Optional: absent on error responses (only request_ref/response_ref/error
        // are returned), so isSuccessful() can short-circuit instead of crashing.
        public ?string $result = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
