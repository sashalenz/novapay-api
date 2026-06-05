<?php

namespace Sashalenz\NovapayApi\ResponseData\Accounts;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class GetPaymentsListResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        // Optional: absent on error responses (only request_ref/response_ref/error
        // are returned), so isSuccessful() can short-circuit instead of crashing.
        public ?string $result = null,
        /**
         * Raw XML string (CDATA) containing <Payments> document.
         * Parse with simplexml_load_string() when needed.
         */
        public ?string $payments = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }

    /**
     * Parse payments XML and return array of payment data arrays.
     */
    public function parsePayments(): array
    {
        if (empty($this->payments)) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->payments, \SimpleXMLElement::class, LIBXML_NOCDATA);

        if ($xml === false) {
            return [];
        }

        $payments = [];
        foreach ($xml->Docs ?? [] as $doc) {
            $payment = [];
            $attrs = $doc->attributes();
            foreach ($attrs as $key => $value) {
                $payment[(string) $key] = (string) $value;
            }
            foreach ($doc->children() as $child) {
                $payment[$child->getName()] = (string) $child;
            }
            $payments[] = $payment;
        }

        return $payments;
    }
}
