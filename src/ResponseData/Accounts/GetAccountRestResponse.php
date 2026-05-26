<?php

namespace Sashalenz\NovapayApi\ResponseData\Accounts;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class GetAccountRestResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        public string $result,
        public float $confirmed_balance,
        public float $available_balance,
        public float $projected_balance,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
