<?php

namespace Sashalenz\NovapayApi\ApiModels;

use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\ResponseData\Payments\CreatePaymentResponse;
use Sashalenz\NovapayApi\ResponseData\Payments\GetRecallPaymentsListResponse;
use Sashalenz\NovapayApi\ResponseData\Payments\RecallPaymentResponse;

class Payments extends BaseModel
{
    private ?string $jwt = null;

    private ?string $principal = null;

    /**
     * Use JWT token for authorization.
     */
    public function withJwt(string $jwt): static
    {
        $this->jwt = $jwt;

        return $this;
    }

    /**
     * Use legacy principal token for authorization.
     */
    public function withPrincipal(string $principal): static
    {
        $this->principal = $principal;

        return $this;
    }

    /**
     * 3.10 — Create a payment.
     *
     * @param  array  $header  Payment header fields:
     *                         OrgDate (dd.mm.yyyy), Code, account_id,
     *                         CreditCodeIBAN, CreditName, CreditStateCode,
     *                         Amount (required), CurrencyTag, Purpose,
     *                         CreditCountry, CreditNotResident, etc.
     *
     * @throws NovapayApiException
     */
    public function create(array $header, ?string $requestRef = null): CreatePaymentResponse
    {
        $data = $this->call('CreatePayment', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
            'header' => $header,
        ]);

        return CreatePaymentResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * 3.11 — Get list of payments available for recall.
     *
     * @throws NovapayApiException
     */
    public function recallableList(?string $requestRef = null): GetRecallPaymentsListResponse
    {
        $data = $this->call('GetRecallPaymentsList', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
        ]);

        $payments = $this->normalizeCollection($data['payments'] ?? [], 'RecallPayments');

        return GetRecallPaymentsListResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'payments' => $payments,
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * 3.12 — Recall a payment by ID.
     *
     * @throws NovapayApiException
     */
    public function recall(
        int $paymentId,
        ?string $info = null,
        ?string $requestRef = null,
    ): RecallPaymentResponse {
        $data = $this->call('RecallPayment', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
            'payment_id' => $paymentId,
            'info' => $info,
        ]);

        return RecallPaymentResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    private function normalizeCollection(mixed $data, string $itemKey): array
    {
        if (! is_array($data) || empty($data)) {
            return [];
        }

        if (! isset($data[$itemKey])) {
            return [];
        }

        $items = $data[$itemKey];

        if (is_array($items) && array_is_list($items)) {
            return $items;
        }

        if (is_array($items)) {
            return [$items];
        }

        return [];
    }
}
