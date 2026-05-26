<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\ResponseData\Auth\JwtAuthResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('returns JwtAuthResponse on successful JWT authentication', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('UserAuthenticationJWT', [
                'request_ref' => 'REQ-123456',
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'jwt' => 'header.payload.signature',
                'expiration' => '**.**.2026 **:**',
                'refresh_token' => 'NEW_REFRESH_TOKEN_XXXXXXXX',
                'public_certificate' => '-----BEGIN RSA PUBLIC KEY-----\nNEW\n-----END RSA PUBLIC KEY-----',
            ])
        ),
    ]);

    $response = NovapayApi::auth()->jwt(
        refreshToken: 'OLD_REFRESH_TOKEN',
        login: 'user_login',
        publicCertificate: '-----BEGIN RSA PUBLIC KEY-----\nOLD\n-----END RSA PUBLIC KEY-----',
    );

    expect($response)->toBeInstanceOf(JwtAuthResponse::class)
        ->and($response->jwt)->toBe('header.payload.signature')
        ->and($response->refresh_token)->toBe('NEW_REFRESH_TOKEN_XXXXXXXX')
        ->and($response->isSuccessful())->toBeTrue();
});

it('sends SOAPAction header for UserAuthenticationJWT', function () {
    Http::fake(function (\Illuminate\Http\Client\Request $request) {
        expect($request->header('SOAPAction'))
            ->toBe(['http://tempuri.org/IClientAPIService/UserAuthenticationJWT']);

        return Http::response(
            $this->soapResponse('UserAuthenticationJWT', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'jwt' => 'x.y.z',
                'expiration' => '01.01.2027',
                'refresh_token' => 'NEW',
                'public_certificate' => 'CERT',
            ])
        );
    });

    NovapayApi::auth()->jwt('TOKEN', 'login', 'CERT');
});

it('authenticates using config credentials', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('UserAuthenticationJWT', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'jwt' => 'config.jwt.token',
                'expiration' => '01.01.2027',
                'refresh_token' => 'NEW_REFRESH',
                'public_certificate' => 'NEW_CERT',
            ])
        ),
    ]);

    $response = NovapayApi::auth()->jwtFromConfig();

    expect($response->jwt)->toBe('config.jwt.token');
});

it('throws NovapayApiException on HTTP error', function () {
    Http::fake(['*' => Http::response('Internal Server Error', 500)]);

    expect(fn () => NovapayApi::auth()->jwt('TOKEN', 'login', 'CERT'))
        ->toThrow(NovapayApiException::class);
});
