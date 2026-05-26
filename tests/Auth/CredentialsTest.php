<?php

use Illuminate\Support\Facades\Http;
use Sashalenz\NovapayApi\HasNovapayCredentials;
use Sashalenz\NovapayApi\NovapayApi;
use Sashalenz\NovapayApi\NovapayCredentials;
use Sashalenz\NovapayApi\ResponseData\Auth\JwtAuthResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

// Анонімна модель, що імплементує інтерфейс
function makeFakeModel(string $login, string $refreshToken, string $cert): HasNovapayCredentials
{
    return new class($login, $refreshToken, $cert) implements HasNovapayCredentials {
        public function __construct(
            private string $login,
            private string $token,
            private string $cert,
        ) {}

        public function getNovapayLogin(): string { return $this->login; }
        public function getNovapayRefreshToken(): string { return $this->token; }
        public function getNovapayPublicCertificate(): string { return $this->cert; }
    };
}

it('authenticates with NovapayCredentials DTO', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('UserAuthenticationJWT', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'jwt' => 'dto.jwt.token',
                'expiration' => '01.01.2027',
                'refresh_token' => 'NEW_REFRESH',
                'public_certificate' => 'NEW_CERT',
            ])
        ),
    ]);

    $credentials = new NovapayCredentials(
        login: 'account_login',
        refreshToken: 'REFRESH_TOKEN',
        publicCertificate: 'CERT',
    );

    $response = NovapayApi::auth()->jwtFromCredentials($credentials);

    expect($response)->toBeInstanceOf(JwtAuthResponse::class)
        ->and($response->jwt)->toBe('dto.jwt.token');
});

it('authenticates with a model via HasNovapayCredentials interface', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('UserAuthenticationJWT', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'jwt' => 'model.jwt.token',
                'expiration' => '01.01.2027',
                'refresh_token' => 'MODEL_NEW_REFRESH',
                'public_certificate' => 'MODEL_NEW_CERT',
            ])
        ),
    ]);

    $model = makeFakeModel('bank_login', 'BANK_REFRESH', 'BANK_CERT');

    $response = NovapayApi::auth()->jwtFromModel($model);

    expect($response->jwt)->toBe('model.jwt.token')
        ->and($response->refresh_token)->toBe('MODEL_NEW_REFRESH');
});

it('authenticates via NovapayApi::authenticateAs() shorthand', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('UserAuthenticationJWT', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'jwt' => 'shorthand.jwt.token',
                'expiration' => '01.01.2027',
                'refresh_token' => 'SH_REFRESH',
                'public_certificate' => 'SH_CERT',
            ])
        ),
    ]);

    $model = makeFakeModel('login', 'REFRESH', 'CERT');

    $response = NovapayApi::authenticateAs($model);

    expect($response->jwt)->toBe('shorthand.jwt.token');
});

it('authenticates via NovapayApi::authenticateWith() shorthand', function () {
    Http::fake([
        '*' => Http::response(
            $this->soapResponse('UserAuthenticationJWT', [
                'response_ref' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'jwt' => 'with.jwt.token',
                'expiration' => '01.01.2027',
                'refresh_token' => 'WITH_REFRESH',
                'public_certificate' => 'WITH_CERT',
            ])
        ),
    ]);

    $credentials = NovapayCredentials::fromConfig();

    $response = NovapayApi::authenticateWith($credentials);

    expect($response->jwt)->toBe('with.jwt.token');
});

it('NovapayCredentials::withUpdatedTokens returns new instance with updated tokens', function () {
    $original = new NovapayCredentials('login', 'OLD_REFRESH', 'OLD_CERT');
    $updated = $original->withUpdatedTokens('NEW_REFRESH', 'NEW_CERT');

    expect($updated->login)->toBe('login')
        ->and($updated->refreshToken)->toBe('NEW_REFRESH')
        ->and($updated->publicCertificate)->toBe('NEW_CERT')
        ->and($original->refreshToken)->toBe('OLD_REFRESH'); // immutable
});

it('NovapayCredentials::fromModel creates credentials from model', function () {
    $model = makeFakeModel('model_login', 'MODEL_REFRESH', 'MODEL_CERT');
    $credentials = NovapayCredentials::fromModel($model);

    expect($credentials->login)->toBe('model_login')
        ->and($credentials->refreshToken)->toBe('MODEL_REFRESH')
        ->and($credentials->publicCertificate)->toBe('MODEL_CERT');
});
