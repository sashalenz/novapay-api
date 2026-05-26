<?php

namespace Sashalenz\NovapayApi\RequestData\Payments;

use Spatie\LaravelData\Data;

class CreatePaymentRequest extends Data
{
    public function __construct(
        public string $auth,               // jwt or principal value (passed via auth() on ApiModel)
        public int $account_id,
        public float $Amount,
        public ?string $OrgDate = null,    // dd.mm.yyyy
        public ?string $Code = null,
        public ?string $CreditCodeIBAN = null,
        public ?string $CreditName = null,
        public ?string $CreditStateCode = null,
        public ?string $CurrencyTag = null,
        public ?string $Purpose = null,
        public ?string $CreditCountry = null,
        public ?bool $CreditNotResident = null,
        public ?string $request_ref = null,
    ) {}
}
