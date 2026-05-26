<?php

namespace Sashalenz\NovapayApi\ApiModels;

use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\ResponseData\Clients\GetClientsListResponse;

class Clients extends BaseModel
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
     * 3.5 — Get list of available enterprises for the authenticated user.
     *
     * @throws NovapayApiException
     */
    public function list(?string $requestRef = null): GetClientsListResponse
    {
        $data = $this->call('GetClientsList', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'jwt' => $this->jwt,
            'principal' => $this->principal,
        ]);

        // Normalize the clients collection
        $clients = $this->normalizeCollection($data['clients'] ?? [], 'Clients');

        return GetClientsListResponse::from([
            'request_ref' => $data['request_ref'] ?? null,
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'clients' => $clients,
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * Normalize an XML collection node that may be a single item or a list.
     *
     * The SOAP parser returns the wrapper element as an associative array:
     *   single item  → ['Clients' => ['id' => 8, ...]]
     *   multiple     → ['Clients' => [['id' => 8, ...], ['id' => 11, ...]]]
     */
    private function normalizeCollection(mixed $data, string $itemKey): array
    {
        if (! is_array($data) || empty($data)) {
            return [];
        }

        if (! isset($data[$itemKey])) {
            return [];
        }

        $items = $data[$itemKey];

        // Already a list of items (multiple nodes parsed by xmlToArray)
        if (is_array($items) && array_is_list($items)) {
            return $items;
        }

        // Single associative item
        if (is_array($items)) {
            return [$items];
        }

        return [];
    }
}
