<?php

namespace Sashalenz\NovapayApi\ApiModels;

use Sashalenz\NovapayApi\Enums\DateType;
use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetAccountExtractResponse;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetAccountRestResponse;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetAccountsListResponse;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetAccountTurnsResponse;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetPaymentsListResponse;

class Accounts extends BaseModel
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
     * 3.6 — Get list of accounts for a given enterprise (client_id).
     *
     * @throws NovapayApiException
     */
    public function list(int $clientId, ?string $requestRef = null): GetAccountsListResponse
    {
        $data = $this->call('GetAccountsList', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
            'client_id' => $clientId,
        ]);

        $accounts = $this->normalizeCollection($data['accounts'] ?? [], 'Accounts');

        return GetAccountsListResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'accounts' => $accounts,
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * 3.7 — Get balance for an account.
     *
     * @throws NovapayApiException
     */
    public function balance(int $accountId, ?string $requestRef = null): GetAccountRestResponse
    {
        $data = $this->call('GetAccountRest', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
            'account_id' => $accountId,
        ]);

        return GetAccountRestResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'confirmed_balance' => (float) ($data['confirmed_balance'] ?? 0),
            'available_balance' => (float) ($data['available_balance'] ?? 0),
            'projected_balance' => (float) ($data['projected_balance'] ?? 0),
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * 3.8 — Get payments list for an account.
     * Returns raw XML (CDATA) in the `payments` field — use parsePayments() to get structured data.
     *
     * @param  string|null  $dateFrom   dd.mm.yyyy
     * @param  string|null  $dateTo     dd.mm.yyyy
     *
     * @throws NovapayApiException
     */
    public function payments(
        ?int $accountId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?DateType $dateType = null,
        ?string $requestRef = null,
    ): GetPaymentsListResponse {
        $data = $this->call('GetPaymentsList', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
            'account_id' => $accountId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'date_type' => $dateType?->value,
        ]);

        return GetPaymentsListResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'payments' => $data['payments'] ?? null,
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * 3.9 — Get account extract (summary statement) in XML.
     * Returns raw XML (CDATA) — use parseExtract() to get structured data.
     *
     * @param  string|null  $dateFrom   dd.mm.yyyy
     * @param  string|null  $dateTo     dd.mm.yyyy
     *
     * @throws NovapayApiException
     */
    public function extract(
        ?int $accountId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $requestRef = null,
    ): GetAccountExtractResponse {
        $data = $this->call('GetAccountExtract', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
            'account_id' => $accountId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        return GetAccountExtractResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'extract' => $data['extract'] ?? null,
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * 3.13 — Get account turnover (daily debit/credit totals).
     *
     * @param  string|null  $dateFrom   dd.mm.yyyy
     * @param  string|null  $dateTo     dd.mm.yyyy
     *
     * @throws NovapayApiException
     */
    public function turns(
        ?int $accountId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $requestRef = null,
    ): GetAccountTurnsResponse {
        $data = $this->call('GetAccountTurns', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
            'account_id' => $accountId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $turns = $this->normalizeCollection($data['turns'] ?? [], 'Turns');

        return GetAccountTurnsResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'turns' => $turns,
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
