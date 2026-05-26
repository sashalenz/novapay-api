<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\ResponseData\Auth\PreAuthResponse;
use Sashalenz\NovapayApi\ResponseData\Auth\RefreshAuthResponse;
use Sashalenz\NovapayApi\ResponseData\Auth\UserAuthResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('returns PreAuthResponse on first-factor authentication', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('PreUserAuthentication', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
                'user_id' => '42',
                'temp_principal' => 'TEMP_PRINCIPAL_VALUE',
                'code_operation_otp' => '718',
                'expiration' => '2025-03-21 16:49:15',
            ])
        ),
    ]);

    $response = NovapayApi::auth()->preAuthenticate('user_login', 'password123');

    expect($response)->toBeInstanceOf(PreAuthResponse::class)
        ->and($response->result)->toBe('ok')
        ->and($response->getUserId())->toBe(42)
        ->and($response->getCodeOperationOtp())->toBe(718)
        ->and($response->isSuccessful())->toBeTrue();
});

it('returns PreAuthResponse with error on wrong password', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('PreUserAuthentication', [
                'response_ref' => 'ffffffff-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'error',
                'user_id' => '0',
                'code_operation_otp' => '0',
                'error' => '<status>logic_error</status><title>Error login for user</title>',
            ])
        ),
    ]);

    $response = NovapayApi::auth()->preAuthenticate('user_login', 'wrong_password');

    expect($response->isSuccessful())->toBeFalse()
        ->and($response->result)->toBe('error');
});

it('returns UserAuthResponse on second-factor authentication', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('UserAuthentication', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
                'principal' => 'LONG_PRINCIPAL_VALUE_XXXXXXXXXXX',
                'expiration' => '2025-03-22 16:01:46',
            ])
        ),
    ]);

    $response = NovapayApi::auth()->authenticate('TEMP_PRINCIPAL', 718, '123456');

    expect($response)->toBeInstanceOf(UserAuthResponse::class)
        ->and($response->result)->toBe('ok')
        ->and($response->principal)->toBe('LONG_PRINCIPAL_VALUE_XXXXXXXXXXX')
        ->and($response->isSuccessful())->toBeTrue();
});

it('returns RefreshAuthResponse on session refresh', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('RefreshUserAuthentication', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'result' => 'ok',
                'new_principal' => 'REFRESHED_PRINCIPAL_VALUE',
                'expiration' => '2025-03-22 16:05:20',
            ])
        ),
    ]);

    $response = NovapayApi::auth()->refresh('OLD_PRINCIPAL');

    expect($response)->toBeInstanceOf(RefreshAuthResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->new_principal)->toBe('REFRESHED_PRINCIPAL_VALUE');
});
