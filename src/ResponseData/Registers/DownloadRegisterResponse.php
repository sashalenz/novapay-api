<?php

namespace Sashalenz\NovapayApi\ResponseData\Registers;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class DownloadRegisterResponse extends Data
{
    public function __construct(
        public string $response_ref,
        public string $result,
        public ?int $id,
        public ?int $scrooge_id,
        public ?string $date_range,
        public ?string $date_from,
        public ?string $date_to,
        public ?string $created_at,
        public ?string $status,
        public ?string $url,
        public ?string $file_type,
        public ?string $file_name,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
