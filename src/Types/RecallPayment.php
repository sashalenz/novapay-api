<?php

namespace Sashalenz\NovapayApi\Types;

use Spatie\LaravelData\Data;

class RecallPayment extends Data
{
    public function __construct(
        public int $id,
        public string $orgdate,
        public ?float $amount,
        public ?string $debitIBAN,
        public ?string $debitname,
        public ?string $debitstatecode,
        public ?string $creditIBAN,
        public ?string $creditname,
        public ?string $credittatecode,
        public ?string $purpose,
    ) {}
}
