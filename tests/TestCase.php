<?php

namespace Sashalenz\NovapayApi\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sashalenz\NovapayApi\NovapayApiServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            NovapayApiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('novapay-api.api_url', 'https://business.novapay.test/Services/ClientAPIService.svc');
        config()->set('novapay-api.login', 'test_login');
        config()->set('novapay-api.refresh_token', 'test_refresh_token');
        config()->set('novapay-api.public_certificate', "-----BEGIN RSA PUBLIC KEY-----\nTEST\n-----END RSA PUBLIC KEY-----");
    }

    /**
     * Build a SOAP envelope response for a given method and result fields.
     *
     * Values that start with '<' are treated as raw XML child nodes.
     * All other values are XML-escaped text content.
     */
    protected function soapResponse(string $method, array $fields): string
    {
        $body = '';
        foreach ($fields as $key => $value) {
            if (is_string($value) && str_starts_with(ltrim($value), '<')) {
                // Raw XML — embed directly (child elements, CDATA, etc.)
                $body .= "<{$key}>{$value}</{$key}>";
            } else {
                // Plain text — escape
                $body .= '<'.$key.'>'.htmlspecialchars((string) $value, ENT_XML1).'</'.$key.'>';
            }
        }

        return <<<XML
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
                <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <{$method}Response xmlns="http://tempuri.org/">
                        <{$method}Result>
                            {$body}
                        </{$method}Result>
                    </{$method}Response>
                </s:Body>
            </s:Envelope>
            XML;
    }

    /**
     * Build a SOAP error response.
     */
    protected function soapErrorResponse(string $method, string $status = 'logic_error', string $title = 'Error'): string
    {
        return $this->soapResponse($method, [
            'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'result' => 'error',
            'error' => "<status>{$status}</status><title>{$title}</title>",
        ]);
    }
}
