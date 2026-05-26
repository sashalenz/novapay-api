<?php

namespace Sashalenz\NovapayApi\ApiModels;

use Sashalenz\NovapayApi\Enums\RegisterFileExtension;
use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\ResponseData\Registers\DownloadRegisterResponse;
use Sashalenz\NovapayApi\ResponseData\Registers\GetRegisterResponse;

class Registers extends BaseModel
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
     * 3.14 — Get (generate) a post-payment register statement for a date range.
     *
     * @param  int  $clientId           Client ID from GetClientsList.
     * @param  string  $from            DD.MM.YYYY
     * @param  string  $into            DD.MM.YYYY
     * @param  RegisterFileExtension  $fileExtension  XLS or CSV.
     * @param  int  $type               Register type (1 = KO register).
     *
     * @throws NovapayApiException
     */
    public function get(
        int $clientId,
        string $from,
        string $into,
        RegisterFileExtension $fileExtension = RegisterFileExtension::XLS,
        int $type = 1,
    ): GetRegisterResponse {
        $data = $this->call('GetRegister', [
            'Type' => $type,
            'ClientId' => $clientId,
            'From' => $from,
            'Into' => $into,
            'FileExtension' => $fileExtension->value,
            'principal' => $this->principal,
            'jwt' => $this->jwt,
        ]);

        return GetRegisterResponse::from([
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'statement_id' => (int) ($data['statement_id'] ?? 0),
            'created_datetime' => $data['created_datetime'] ?? '',
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }

    /**
     * 3.15 — Download a post-payment register statement by ID.
     *
     * @param  int  $id     Statement ID from GetRegister response.
     * @param  int  $type   Register type (1 = KO register).
     *
     * @throws NovapayApiException
     */
    public function download(int $id, int $type = 1): DownloadRegisterResponse
    {
        $data = $this->call('DownloadRegister', [
            'Type' => $type,
            'Id' => $id,
            'principal' => $this->principal,
            'jwt' => $this->jwt,
        ]);

        return DownloadRegisterResponse::from([
            'response_ref' => $data['response_ref'] ?? '',
            'result' => $data['result'] ?? 'error',
            'id' => isset($data['id']) ? (int) $data['id'] : null,
            'scrooge_id' => isset($data['scrooge_id']) ? (int) $data['scrooge_id'] : null,
            'date_range' => $data['date_range'] ?? null,
            'date_from' => $data['date_from'] ?? null,
            'date_to' => $data['date_to'] ?? null,
            'created_at' => $data['created_at'] ?? null,
            'status' => $data['status'] ?? null,
            'url' => $data['url'] ?? null,
            'file_type' => $data['file_type'] ?? null,
            'file_name' => $data['file_name'] ?? null,
            'error' => isset($data['error']) ? $data['error'] : null,
        ]);
    }
}
