<?php

namespace Sashalenz\NovapayApi;

class NovapayCredentials
{
    public function __construct(
        public readonly string $login,
        public readonly string $refreshToken,
        public readonly string $publicCertificate,
    ) {}

    /**
     * Create from any object that implements HasNovapayCredentials.
     */
    public static function fromModel(HasNovapayCredentials $model): self
    {
        return new self(
            login: $model->getNovapayLogin(),
            refreshToken: $model->getNovapayRefreshToken(),
            publicCertificate: $model->getNovapayPublicCertificate(),
        );
    }

    /**
     * Create from config (default .env credentials).
     */
    public static function fromConfig(): self
    {
        return new self(
            login: config('novapay-api.login'),
            refreshToken: config('novapay-api.refresh_token'),
            publicCertificate: config('novapay-api.public_certificate'),
        );
    }

    /**
     * Return a new instance with updated tokens after successful JWT auth.
     */
    public function withUpdatedTokens(string $refreshToken, string $publicCertificate): self
    {
        return new self(
            login: $this->login,
            refreshToken: $refreshToken,
            publicCertificate: $publicCertificate,
        );
    }
}
