<?php

namespace Sashalenz\NovapayApi;

use Sashalenz\NovapayApi\ApiModels\Accounts;
use Sashalenz\NovapayApi\ApiModels\Auth;
use Sashalenz\NovapayApi\ApiModels\Clients;
use Sashalenz\NovapayApi\ApiModels\Payments;
use Sashalenz\NovapayApi\ApiModels\Registers;

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
}
