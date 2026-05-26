<?php

namespace Sashalenz\NovapayApi\Types;

use Sashalenz\NovapayApi\Enums\AccountStatus;
use Spatie\LaravelData\Data;

class Account extends Data
{
    public function __construct(
        public int $id,
        public string $IBAN,
        public string $name,
        public string $currency,
        public int $status,
        public ?AccountStatus $statuscode = null,
    ) {}
}
