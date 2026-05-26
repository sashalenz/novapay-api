<?php

namespace Sashalenz\NovapayApi;

use Sashalenz\NovapayApi\ApiModels\Accounts;
use Sashalenz\NovapayApi\ApiModels\Auth;
use Sashalenz\NovapayApi\ApiModels\Clients;
use Sashalenz\NovapayApi\ApiModels\Payments;
use Sashalenz\NovapayApi\ApiModels\Registers;
use Sashalenz\NovapayApi\Exceptions\NovapayApiException;
use Sashalenz\NovapayApi\ResponseData\Auth\JwtAuthResponse;

class NovapayApi
{
    /**
     * Authentication methods (JWT, legacy login/password, session refresh).
     */
    public static function auth(): Auth
    {
        return new Auth;
    }

    /**
     * Client (enterprise) listing.
     */
    public static function clients(): Clients
    {
        return new Clients;
    }

    /**
     * Account operations: list, balance, payments, extract, turns.
     */
    public static function accounts(): Accounts
    {
        return new Accounts;
    }

    /**
     * Payment operations: create, recall, list.
     */
    public static function payments(): Payments
    {
        return new Payments;
    }

    /**
     * Post-payment register operations.
     */
    public static function registers(): Registers
    {
        return new Registers;
    }

    /**
     * Authenticate with a NovapayCredentials DTO and return the JWT response.
     * Shorthand for NovapayApi::auth()->jwtFromCredentials($credentials).
     *
     * @throws NovapayApiException
     */
    public static function authenticateWith(NovapayCredentials $credentials): JwtAuthResponse
    {
        return static::auth()->jwtFromCredentials($credentials);
    }

    /**
     * Authenticate with a model that implements HasNovapayCredentials.
     * Shorthand for NovapayApi::auth()->jwtFromModel($model).
     *
     * @throws NovapayApiException
     */
    public static function authenticateAs(HasNovapayCredentials $model): JwtAuthResponse
    {
        return static::auth()->jwtFromModel($model);
    }
}
