<?php

namespace Sashalenz\NovapayApi\Types;

use Spatie\LaravelData\Data;

class Client extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $statecode,       // ЄДРПОУ / РНОКПП
        public ?string $countrycode,
    ) {}
}
