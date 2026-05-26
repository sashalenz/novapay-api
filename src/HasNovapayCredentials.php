<?php

namespace Sashalenz\NovapayApi;

/**
 * Implement this interface on any Eloquent model that stores NovaPay credentials.
 *
 * Example:
 *
 *   class BankAccount extends Model implements HasNovapayCredentials
 *   {
 *       public function getNovapayLogin(): string
 *       {
 *           return $this->novapay_login;
 *       }
 *
 *       public function getNovapayRefreshToken(): string
 *       {
 *           return $this->novapay_refresh_token;
 *       }
 *
 *       public function getNovapayPublicCertificate(): string
 *       {
 *           return $this->novapay_public_certificate;
 *       }
 *
 *       public function updateNovapayTokens(string $refreshToken, string $publicCertificate): void
 *       {
 *           $this->update([
 *               'novapay_refresh_token'       => $refreshToken,
 *               'novapay_public_certificate'  => $publicCertificate,
 *           ]);
 *       }
 *   }
 */
interface HasNovapayCredentials
{
    public function getNovapayLogin(): string;

    public function getNovapayRefreshToken(): string;

    public function getNovapayPublicCertificate(): string;
}
