<?php

namespace Sashalenz\NovapayApi\ResponseData\Accounts;

use Sashalenz\NovapayApi\Types\ApiError;
use Spatie\LaravelData\Data;

class GetAccountExtractResponse extends Data
{
    public function __construct(
        public ?string $request_ref,
        public string $response_ref,
        // Optional: absent on error responses (only request_ref/response_ref/error
        // are returned), so isSuccessful() can short-circuit instead of crashing.
        public ?string $result = null,
        /**
         * Raw XML string (CDATA) containing <Extract> document with ExtractHead + Docs.
         */
        public ?string $extract = null,
        public ?ApiError $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->result === 'ok';
    }

    /**
     * Parse extract XML and return structured array with head and payments.
     */
    public function parseExtract(): array
    {
        if (empty($this->extract)) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->extract, \SimpleXMLElement::class, LIBXML_NOCDATA);

        if ($xml === false) {
            return [];
        }

        $result = ['head' => [], 'payments' => []];

        foreach ($xml->ExtractHead ?? [] as $head) {
            $headData = [];
            foreach ($head->children() as $child) {
                $headData[$child->getName()] = (string) $child;
            }
            $result['head'][] = $headData;
        }

        foreach ($xml->Docs ?? [] as $doc) {
            $payment = [];
            $attrs = $doc->attributes();
            foreach ($attrs as $key => $value) {
                $payment[(string) $key] = (string) $value;
            }
            foreach ($doc->children() as $child) {
                $payment[$child->getName()] = (string) $child;
            }
            $result['payments'][] = $payment;
        }

        return $result;
    }
}
