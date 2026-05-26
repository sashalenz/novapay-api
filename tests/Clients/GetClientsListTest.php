<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\ResponseData\Clients\GetClientsListResponse;
use Sashalenz\NovapayApi\Types\Client;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('returns clients list with JWT auth', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetClientsList', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
                'clients' => '<Clients><id>8</id><name>Тестовий ФОП13</name><statecode>2871403864</statecode><countrycode></countrycode></Clients>
                              <Clients><id>11</id><name>ФОП Тест Роман Миколайович</name><statecode>3351010796</statecode><countrycode></countrycode></Clients>',
            ])
        ),
    ]);

    $response = NovapayApi::clients()
        ->withJwt('test.jwt.token')
        ->list();

    expect($response)->toBeInstanceOf(GetClientsListResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->clients)->toHaveCount(2)
        ->and($response->clients->first())->toBeInstanceOf(Client::class)
        ->and($response->clients->first()->id)->toEqual(8)
        ->and($response->clients->first()->name)->toBe('Тестовий ФОП13');
});

it('sends SOAPAction header for GetClientsList', function () {
    Http::fake(function (\Illuminate\Http\Client\Request $request) {
        expect($request->header('SOAPAction'))
            ->toBe(['http://tempuri.org/IClientAPIService/GetClientsList']);

        return Http::response(
            $this->soapResponse('GetClientsList', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
                'clients' => '',
            ])
        );
    });

    NovapayApi::clients()->withJwt('token')->list();
});

it('returns error result when principal is invalid', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapErrorResponse('GetClientsList', 'system_error', 'Unable to validate data.')
        ),
    ]);

    $response = NovapayApi::clients()
        ->withPrincipal('INVALID_PRINCIPAL')
        ->list();

    expect($response->isSuccessful())->toBeFalse()
        ->and($response->result)->toBe('error');
});
