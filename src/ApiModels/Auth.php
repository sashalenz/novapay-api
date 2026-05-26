<?php

namespace Sashalenz\NovapayApi\ApiModels;

use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\HasNovapayCredentials;
use Sashalenz\NovapayApi\NovapayCredentials;
use Sashalenz\NovapayApi\ResponseData\Auth\JwtAuthResponse;
use Sashalenz\NovapayApi\ResponseData\Auth\PreAuthResponse;
use Sashalenz\NovapayApi\ResponseData\Auth\RefreshAuthResponse;
use Sashalenz\NovapayApi\ResponseData\Auth\UserAuthResponse;

class Auth extends BaseModel
{
    /**
     * 3.1 — JWT Authorization.
     * Recommended authentication method (single-use refresh token rotation).
     *
     * @throws NovapayApiException
     */
    public function jwt(
        string $refreshToken,
        string $login,
        string $publicCertificate,
        ?string $requestRef = null,
    ): JwtAuthResponse {
        $data = $this->call('UserAuthenticationJWT', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'refresh_token' => $refreshToken,
            'login' => $login,
            'public_certificate' => $publicCertificate,
        ]);

        return JwtAuthResponse::from($data);
    }

    /**
     * 3.1 — JWT Authorization using config credentials (.env).
     *
     * @throws NovapayApiException
     */
    public function jwtFromConfig(?string $requestRef = null): JwtAuthResponse
    {
        return $this->jwtFromCredentials(NovapayCredentials::fromConfig(), $requestRef);
    }

    /**
     * 3.1 — JWT Authorization using a NovapayCredentials DTO.
     *
     * @throws NovapayApiException
     */
    public function jwtFromCredentials(NovapayCredentials $credentials, ?string $requestRef = null): JwtAuthResponse
    {
        return $this->jwt(
            refreshToken: $credentials->refreshToken,
            login: $credentials->login,
            publicCertificate: $credentials->publicCertificate,
            requestRef: $requestRef,
        );
    }

    /**
     * 3.1 — JWT Authorization using a model that implements HasNovapayCredentials.
     *
     * @throws NovapayApiException
     */
    public function jwtFromModel(HasNovapayCredentials $model, ?string $requestRef = null): JwtAuthResponse
    {
        return $this->jwtFromCredentials(NovapayCredentials::fromModel($model), $requestRef);
    }

    /**
     * 3.2 — First-factor authentication (login + password).
     * Will be deprecated on 31.08.2026.
     *
     * @throws NovapayApiException
     */
    public function preAuthenticate(
        string $login,
        string $password,
        ?string $phone = null,
        ?string $requestRef = null,
    ): PreAuthResponse {
        $data = $this->call('PreUserAuthentication', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'login' => $login,
            'password' => $password,
            'phone' => $phone,
        ]);

        return PreAuthResponse::from($data);
    }

    /**
     * 3.3 — Second-factor authentication (OTP confirmation).
     * Will be deprecated on 31.08.2026.
     *
     * @throws NovapayApiException
     */
    public function authenticate(
        string $tempPrincipal,
        int $codeOperationOtp,
        ?string $otpPassword = null,
        ?string $requestRef = null,
    ): UserAuthResponse {
        $data = $this->call('UserAuthentication', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'temp_principal' => $tempPrincipal,
            'code_operation_otp' => $codeOperationOtp,
            'otp_password' => $otpPassword,
        ]);

        return UserAuthResponse::from($data);
    }

    /**
     * 3.4 — Refresh session (principal) lifetime.
     * Will be deprecated on 31.08.2026.
     *
     * @throws NovapayApiException
     */
    public function refresh(
        string $principal,
        ?string $requestRef = null,
    ): RefreshAuthResponse {
        $data = $this->call('RefreshUserAuthentication', [
            'request_ref' => $requestRef ?? $this->generateRequestRef(),
            'principal' => $principal,
        ]);

        return RefreshAuthResponse::from($data);
    }
}
