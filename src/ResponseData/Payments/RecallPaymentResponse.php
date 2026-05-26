<?php

namespace Sashalenz\NovapayApi\ResponseData\Payments;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class RecallPaymentResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        public string $result,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
