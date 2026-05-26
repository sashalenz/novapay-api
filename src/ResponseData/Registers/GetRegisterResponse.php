<?php

namespace Sashalenz\NovapayApi\ResponseData\Registers;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class GetRegisterResponse extends Data
{
    public function __construct(
        public string $response_ref,
        public string $result,
        public int $statement_id,
        public string $created_datetime,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
