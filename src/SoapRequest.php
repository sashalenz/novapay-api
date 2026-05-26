<?php

namespace Sashalenz\NovapayApi;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use SimpleXMLElement;

final class SoapRequest
{
    // Note: typed class constants require PHP 8.3+, so we intentionally omit types here.
    private const SOAP_NS = 'http://schemas.xmlsoap.org/soap/envelope/';

    private const TEM_NS = 'http://tempuri.org/';

    private const ACTION_BASE = 'http://tempuri.org/IClientAPIService/';

    public function __construct(
        private readonly string $method,
        private readonly array $params,
        private readonly ?string $proxy = null,
    ) {}

    /**
     * @throws NovapayApiException
     */
    public function make(): array
    {
        $xml = $this->buildEnvelope();

        try {
            $response = Http::timeout((int) config('novapay-api.timeout', 30))
                ->withHeaders(['SOAPAction' => self::ACTION_BASE.$this->method])
                ->when(
                    ! is_null($this->proxy),
                    fn ($request) => $request->withOptions(['proxy' => $this->proxy])
                )
                ->withBody($xml, 'text/xml; charset=utf-8')
                ->post(config('novapay-api.api_url'))
                ->throw()
                ->body();

            return $this->parseResponse($response);
        } catch (RequestException $e) {
            throw new NovapayApiException('NovaPay API Exception: '.$e->getMessage(), $e->getCode());
        }
    }

    private function buildEnvelope(): string
    {
        $soapNs = self::SOAP_NS;
        $temNs = self::TEM_NS;
        $params = $this->buildParams($this->params);
        $method = $this->method;

        return <<<XML
            <soapenv:Envelope xmlns:soapenv="{$soapNs}" xmlns:tem="{$temNs}">
                <soapenv:Header/>
                <soapenv:Body>
                    <tem:{$method}>
                        <tem:request>
                            {$params}
                        </tem:request>
                    </tem:{$method}>
                </soapenv:Body>
            </soapenv:Envelope>
            XML;
    }

    private function buildParams(array $params): string
    {
        $xml = '';

        foreach ($params as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if (is_array($value)) {
                $xml .= "<tem:{$key}>{$this->buildParams($value)}</tem:{$key}>";
            } elseif (is_bool($value)) {
                $xml .= "<tem:{$key}>".($value ? 'true' : 'false')."</tem:{$key}>";
            } else {
                $xml .= "<tem:{$key}>".htmlspecialchars((string) $value, ENT_XML1)."</tem:{$key}>";
            }
        }

        return $xml;
    }

    /**
     * @throws NovapayApiException
     */
    private function parseResponse(string $body): array
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NOCDATA);

        if ($xml === false) {
            $error = libxml_get_last_error();
            throw new NovapayApiException('Failed to parse NovaPay SOAP response: '.($error ? $error->message : 'unknown'));
        }

        $xml->registerXPathNamespace('s', self::SOAP_NS);
        $xml->registerXPathNamespace('tem', self::TEM_NS);

        // Find Body — works with both s:Body and s:Body namespace variations
        $bodyResults = $xml->xpath('//*[local-name()="Body"]');
        $bodyNode = is_array($bodyResults) && isset($bodyResults[0]) ? $bodyResults[0] : null;

        if ($bodyNode === null) {
            throw new NovapayApiException('Invalid SOAP response: missing Body element');
        }

        // Find the Result node: e.g. UserAuthenticationJWTResult, GetClientsListResult
        $resultNodeName = $this->method.'Result';
        $resultNodes = $xml->xpath("//*[local-name()='{$resultNodeName}']");
        $resultNode = is_array($resultNodes) && isset($resultNodes[0]) ? $resultNodes[0] : null;

        if ($resultNode === null) {
            // Fallback: any node containing "Result"
            $fallback = $xml->xpath('//*[contains(local-name(), "Result")]');
            $resultNode = is_array($fallback) && isset($fallback[0]) ? $fallback[0] : null;
        }

        if ($resultNode === null) {
            throw new NovapayApiException('Invalid SOAP response: cannot find Result node');
        }

        return $this->xmlToArray($resultNode);
    }

    private function xmlToArray(SimpleXMLElement $element): array
    {
        $result = [];

        foreach ($element->children() as $child) {
            $name = $child->getName();

            if ($child->count() > 0) {
                $childValue = $this->xmlToArray($child);
            } else {
                $childValue = (string) $child;
            }

            if (array_key_exists($name, $result)) {
                // Convert to list on second occurrence
                if (! is_array($result[$name]) || ! array_is_list($result[$name])) {
                    $result[$name] = [$result[$name]];
                }
                $result[$name][] = $childValue;
            } else {
                $result[$name] = $childValue;
            }
        }

        return $result;
    }
}
