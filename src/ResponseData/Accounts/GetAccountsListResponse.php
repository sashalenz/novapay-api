<?php

namespace Sashalenz\NovapayApi\ResponseData\Accounts;

use Sashalenz\NovapayApi\Types\Account;
use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class GetAccountsListResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        // Optional: absent on error responses (only request_ref/response_ref/error
        // are returned), so isSuccessful() can short-circuit instead of crashing.
        public ?string $result = null,
        #[DataCollectionOf(Account::class)]
        public ?DataCollection $accounts = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
