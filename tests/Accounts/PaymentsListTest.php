<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetPaymentsListResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('returns payments list with raw XML', function () {
    $paymentsXml = '<Payments xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><Docs Amount="2.00" CurrencyTag="UAH"><OrgDate>11.10.2024</OrgDate><DayDate>11.10.2024</DayDate><Code>24</Code><CreditName>Тест Роман Миколайович</CreditName><Purpose>Переказ коштів на інший власний рахунок ФОП</Purpose><StatusDocumentId>1</StatusDocumentId><StatusDocument>New</StatusDocument></Docs></Payments>';

    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetPaymentsList', [
                'response_ref' => 'ef94e28d-4ea2-4ba2-927f-d166f2f0afe6',
                'result' => 'ok',
                'payments' => "<![CDATA[{$paymentsXml}]]>",
            ])
        ),
    ]);

    $response = NovapayApi::accounts()
        ->withJwt('test.jwt.token')
        ->payments(accountId: 49, dateFrom: '01.10.2024', dateTo: '31.10.2024');

    expect($response)->toBeInstanceOf(GetPaymentsListResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->payments)->not->toBeNull();
});

it('returns empty payments list when no payments found', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetPaymentsList', [
                'response_ref' => '9b161df5-1482-410f-a4c7-9387b25d320a',
                'result' => 'ok',
                'payments' => '&lt;Payments xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" /&gt;',
            ])
        ),
    ]);

    $response = NovapayApi::accounts()
        ->withJwt('test.jwt.token')
        ->payments(dateFrom: '01.01.2020', dateTo: '01.01.2020');

    expect($response->isSuccessful())->toBeTrue();
});
