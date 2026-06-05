<?php

namespace Sashalenz\NovapayApi\ResponseData\Accounts;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class GetAccountRestResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        // Optional: absent on error responses (only request_ref/response_ref/error
        // are returned), so isSuccessful() can short-circuit instead of crashing.
        public ?string $result = null,
        public ?float $confirmed_balance = null,
        public ?float $available_balance = null,
        public ?float $projected_balance = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
