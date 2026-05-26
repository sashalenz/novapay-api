<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\ResponseData\Payments\CreatePaymentResponse;
use Sashalenz\NovapayApi\ResponseData\Payments\GetRecallPaymentsListResponse;
use Sashalenz\NovapayApi\ResponseData\Payments\RecallPaymentResponse;
use Sashalenz\NovapayApi\Types\RecallPayment;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('creates a payment successfully', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('CreatePayment', [
                'response_ref' => '3f8ec65b-d299-43ca-8ed2-f6a4a6c391ef',
                'result' => 'ok',
            ])
        ),
    ]);

    $response = NovapayApi::payments()
        ->withJwt('test.jwt.token')
        ->create([
            'account_id' => 49,
            'OrgDate' => '14.11.2024',
            'CreditCodeIBAN' => 'UA293587000000673200000000012',
            'CreditName' => 'ФОП Тест Тестовий',
            'CreditStateCode' => '1234567899',
            'Amount' => '123.1',
            'CurrencyTag' => 'UAH',
            'Purpose' => 'Сплата товару згідо рахунку №123',
        ]);

    expect($response)->toBeInstanceOf(CreatePaymentResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->response_ref)->toBe('3f8ec65b-d299-43ca-8ed2-f6a4a6c391ef');
});

it('sends SOAPAction header for CreatePayment', function () {
    Http::fake(function (\Illuminate\Http\Client\Request $request) {
        expect($request->header('SOAPAction'))
            ->toBe(['http://tempuri.org/IClientAPIService/CreatePayment']);

        return Http::response(
            $this->soapResponse('CreatePayment', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
            ])
        );
    });

    NovapayApi::payments()->withJwt('jwt')->create(['Amount' => 100, 'account_id' => 1]);
});

it('returns recallable payments list', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetRecallPaymentsList', [
                'response_ref' => '610da96f-c06a-4c41-8596-32e2ef741dd9',
                'result' => 'ok',
                'payments' => '<RecallPayments><id>1159</id><orgdate>18.02.2025</orgdate><amount>53.0000</amount><debitIBAN>UA29358710000067320000000186</debitIBAN><debitname>ФОП Тест Роман</debitname><debitstatecode>1234567899</debitstatecode><creditIBAN>UA12935871000000673200000000190</creditIBAN><creditname>ФОП Тест Тестовий</creditname><credittatecode>1234567899</credittatecode><purpose>Оплата за товар</purpose></RecallPayments>',
            ])
        ),
    ]);

    $response = NovapayApi::payments()
        ->withJwt('test.jwt.token')
        ->recallableList();

    expect($response)->toBeInstanceOf(GetRecallPaymentsListResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->payments)->toHaveCount(1)
        ->and($response->payments->first())->toBeInstanceOf(RecallPayment::class)
        ->and($response->payments->first()->id)->toEqual(1159)
        ->and((float) $response->payments->first()->amount)->toEqual(53.0);
});

it('recalls a payment successfully', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('RecallPayment', [
                'response_ref' => '203418de-2298-48aa-b02G-3171aa5fa341',
                'result' => 'ok',
            ])
        ),
    ]);

    $response = NovapayApi::payments()
        ->withJwt('test.jwt.token')
        ->recall(paymentId: 720, info: 'Тест відкликання за допомогою API');

    expect($response)->toBeInstanceOf(RecallPaymentResponse::class)
        ->and($response->isSuccessful())->toBeTrue();
});

it('returns error when payment cannot be recalled due to status', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapErrorResponse('RecallPayment', 'logic_error', 'Incorrect payment status.')
        ),
    ]);

    $response = NovapayApi::payments()
        ->withJwt('test.jwt.token')
        ->recall(paymentId: 999);

    expect($response->isSuccessful())->toBeFalse();
});
