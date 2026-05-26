<?php

namespace Sashalenz\NovapayApi\Types;

use Spatie\LaravelData\Data;

class AccountTurn extends Data
{
    public function __construct(
        public ?string $date,
        public ?string $IBAN,
        public ?string $Currency,
        public ?float $InRest,
        public ?float $InRestMain,
        public ?float $CrncyDebit,
        public ?float $MainDebit,
        public ?float $CrncyCredit,
        public ?float $MainCredit,
        public ?float $CrncyRest,
        public ?float $MainRest,
    ) {}
}
