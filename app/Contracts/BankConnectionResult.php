<?php

namespace App\Contracts;

class BankConnectionResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $accessToken = null,
        public readonly ?string $refreshToken = null,
        public readonly ?\DateTimeInterface $tokenExpiresAt = null,
        public readonly ?string $externalLinkId = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public static function ok(
        string $accessToken,
        string $refreshToken,
        \DateTimeInterface $tokenExpiresAt,
        string $externalLinkId,
    ): self {
        return new self(
            success: true,
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            tokenExpiresAt: $tokenExpiresAt,
            externalLinkId: $externalLinkId,
        );
    }

    public static function failed(string $errorMessage): self
    {
        return new self(success: false, errorMessage: $errorMessage);
    }
}
