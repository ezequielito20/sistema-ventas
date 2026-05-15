<?php

namespace App\Contracts;

class ProductIdentificationResult
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $brand = null,
        public readonly float $confidence = 0.0,
        public readonly array $candidates = [],
    ) {}

    public function isReliable(): bool
    {
        return $this->confidence >= config('home.ai_confidence_threshold', 0.75);
    }
}
