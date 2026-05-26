<?php

namespace Sashalenz\NovapayApi\RequestData\Accounts;

use Spatie\LaravelData\Data;

class GetAccountsListRequest extends Data
{
    public function __construct(
        public string $client_id,
        public ?string $jwt = null,
        public ?string $principal = null,
        public ?string $request_ref = null,
    ) {}
}
