<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\Enums\AccountStatus;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetAccountRestResponse;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetAccountsListResponse;
use Sashalenz\NovapayApi\ResponseData\Accounts\GetAccountTurnsResponse;
use Sashalenz\NovapayApi\Types\Account;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('returns accounts list for client', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetAccountsList', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
                'accounts' => '<Accounts><id>49</id><IBAN>UA293587100000673200000000190</IBAN><name>ФОП Тестовий</name><currency>UAH</currency><status>1</status><statuscode>Active</statuscode></Accounts>
                               <Accounts><id>51</id><IBAN>UA653587100000673250000000191</IBAN><name>ФОП Тестовий</name><currency>UAH</currency><status>4</status><statuscode>OnApproval</statuscode></Accounts>',
            ])
        ),
    ]);

    $response = NovapayApi::accounts()
        ->withJwt('test.jwt.token')
        ->list(clientId: 8);

    expect($response)->toBeInstanceOf(GetAccountsListResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->accounts)->toHaveCount(2)
        ->and($response->accounts->first())->toBeInstanceOf(Account::class)
        ->and($response->accounts->first()->id)->toEqual(49)
        ->and($response->accounts->first()->IBAN)->toBe('UA293587100000673200000000190')
        ->and($response->accounts->first()->statuscode)->toBe(AccountStatus::Active);
});

it('returns account balance', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetAccountRest', [
                'response_ref' => 'ad82163f-f579-4a53-8dc4-be6ea6355eb5',
                'confirmed_balance' => '6593.5800',
                'available_balance' => '6582.3100',
                'projected_balance' => '6593.5800',
                'result' => 'ok',
            ])
        ),
    ]);

    $response = NovapayApi::accounts()
        ->withJwt('test.jwt.token')
        ->balance(accountId: 49);

    expect($response)->toBeInstanceOf(GetAccountRestResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->confirmed_balance)->toEqual(6593.58)
        ->and($response->available_balance)->toEqual(6582.31)
        ->and($response->projected_balance)->toEqual(6593.58);
});

it('returns error when account not accessible', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapErrorResponse('GetAccountRest', 'logic_error', 'User not access to account.')
        ),
    ]);

    $response = NovapayApi::accounts()
        ->withJwt('test.jwt.token')
        ->balance(accountId: 999);

    expect($response->isSuccessful())->toBeFalse();
});

it('returns account turns (turnover)', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('GetAccountTurns', [
                'response_ref' => '3c05c887-2a50-4138-a30e-4d96817657ccc',
                'result' => 'ok',
                'turns' => '<Turns><date>14.10.2024</date><IBAN>UA29358710000067320000000186</IBAN><Currency>UAH</Currency><InRest>6612.8800</InRest><InRestMain>0.0000</InRestMain><CrncyDebit>0.0000</CrncyDebit><MainDebit>0.0000</MainDebit><CrncyCredit>0.0000</CrncyCredit><MainCredit>0.0000</MainCredit><CrncyRest>6601.8800</CrncyRest><MainRest>0.0000</MainRest></Turns>',
            ])
        ),
    ]);

    $response = NovapayApi::accounts()
        ->withJwt('test.jwt.token')
        ->turns(accountId: 49, dateFrom: '14.10.2024', dateTo: '15.10.2024');

    expect($response)->toBeInstanceOf(GetAccountTurnsResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->turns)->toHaveCount(1)
        ->and($response->turns->first()->IBAN)->toBe('UA29358710000067320000000186')
        ->and((float) $response->turns->first()->InRest)->toEqual(6612.88);
});
