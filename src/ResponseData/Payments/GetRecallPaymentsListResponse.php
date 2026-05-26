<?php

namespace Sashalenz\NovapayApi\ResponseData\Payments;

use Sashalenz\NovapayApi\Types\ApiError;
use Sashalenz\NovapayApi\Types\RecallPayment;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class GetRecallPaymentsListResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        public string $result,
        #[DataCollectionOf(RecallPayment::class)]
        public DataCollection $payments,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }
}
