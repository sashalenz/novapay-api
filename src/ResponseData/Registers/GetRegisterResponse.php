<?php

namespace Sashalenz\NovapayApi\ResponseData\Registers;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class GetRegisterResponse extends Data
{
    public function __construct(
        public string $response_ref,
        // Optional: absent on error responses (only response_ref/error are
        // returned), so isSuccessful() can short-circuit instead of crashing.
        public ?string $result = null,
        public ?int $statement_id = null,
        public ?string $created_datetime = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
