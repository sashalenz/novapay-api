<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\Enums\RegisterFileExtension;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\ResponseData\Registers\DownloadRegisterResponse;
use Sashalenz\NovapayApi\ResponseData\Registers\GetRegisterResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('generates a register statement and returns statement_id', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetRegister', [
                'response_ref' => '5ca8ada9-c026-42d3-ae33-20ff3c2d640d',
                'result' => 'ok',
                'statement_id' => '327',
                'created_datetime' => '2025-05-21T08:46:33.844+00:00',
            ])
        ),
    ]);

    $response = NovapayApi::registers()
        ->withJwt('test.jwt.token')
        ->get(clientId: 8, from: '01.05.2025', into: '21.05.2025', fileExtension: RegisterFileExtension::XLS);

    expect($response)->toBeInstanceOf(GetRegisterResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->statement_id)->toEqual(327)
        ->and($response->created_datetime)->toBe('2025-05-21T08:46:33.844+00:00');
});

it('downloads a register by statement ID', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('DownloadRegister', [
                'response_ref' => '232eb19c-112b-4e35-8723-83838d00441c',
                'result' => 'ok',
                'id' => '327',
                'scrooge_id' => '999',
                'date_range' => 'custom',
                'date_from' => '2025-05-01',
                'date_to' => '2025-05-21',
                'created_at' => '2025-05-21T13:46:54.058+00:00',
                'status' => 'created',
                'url' => 'https://novapay.ua/files/register.xlsx',
                'file_type' => 'XLS',
                'file_name' => 'Реєстр переказів з 2025-05-01 до 2025-05-21.xlsx',
            ])
        ),
    ]);

    $response = NovapayApi::registers()
        ->withJwt('test.jwt.token')
        ->download(id: 327);

    expect($response)->toBeInstanceOf(DownloadRegisterResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->id)->toEqual(327)
        ->and($response->status)->toBe('created')
        ->and($response->file_type)->toBe('XLS')
        ->and($response->url)->toBe('https://novapay.ua/files/register.xlsx');
});

it('sends correct SOAPAction for GetRegister', function () {
    Http::fake(function (\Illuminate\Http\Client\Request $request) {
        expect($request->header('SOAPAction'))
            ->toBe(['http://tempuri.org/IClientAPIService/GetRegister']);

        return Http::response(
            $this->soapResponse('GetRegister', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
                'statement_id' => '1',
                'created_datetime' => '2025-01-01T00:00:00+00:00',
            ])
        );
    });

    NovapayApi::registers()->withJwt('jwt')->get(1, '01.01.2025', '31.01.2025');
});
