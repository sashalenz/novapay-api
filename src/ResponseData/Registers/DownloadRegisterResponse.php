<?php

namespace Sashalenz\NovapayApi\ResponseData\Registers;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class DownloadRegisterResponse extends Data
{
    public function __construct(
        public string $response_ref,
        // Optional: absent on error responses (only response_ref/error are
        // returned), so isSuccessful() can short-circuit instead of crashing.
        public ?string $result = null,
        public ?int $id = null,
        public ?int $scrooge_id = null,
        public ?string $date_range = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public ?string $created_at = null,
        public ?string $status = null,
        public ?string $url = null,
        public ?string $file_type = null,
        public ?string $file_name = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
