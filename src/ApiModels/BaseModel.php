<?php

namespace Sashalenz\NovapayApi\ApiModels;

use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\SoapRequest;

abstract class BaseModel
{
    protected ?string $proxy = null;

    public function proxy(string $proxy): static
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * @throws NovapayApiException
     */
    protected function call(string $method, array $params): array
    {
        return (new SoapRequest(
            method: $method,
            params: array_filter($params, fn ($v) => ! is_null($v)),
            proxy: $this->proxy,
        ))->make();
    }

    protected function generateRequestRef(): string
    {
        return 'REQ-'.strtoupper(substr(md5(uniqid('', true)), 0, 12));
    }
}
