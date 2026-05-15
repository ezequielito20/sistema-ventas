<?php

namespace App\Contracts;

use App\Models\Home\HomeProduct;
use App\Models\Home\HomeProductMovement;

class DeductResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?HomeProduct $product = null,
        public readonly ?HomeProductMovement $movement = null,
        public readonly ?string $message = null,
        public readonly ?string $errorCode = null,
    ) {}

    public static function success(HomeProduct $product, HomeProductMovement $movement): self
    {
        return new self(success: true, product: $product, movement: $movement);
    }

    public static function notFound(string $message): self
    {
        return new self(success: false, message: $message, errorCode: 'NOT_FOUND');
    }

    public static function insufficientStock(string $productName, int $currentStock): self
    {
        return new self(
            success: false,
            message: "\"{$productName}\" tiene solo {$currentStock} unidad(es). No se puede descontar.",
            errorCode: 'INSUFFICIENT_STOCK',
        );
    }

    public static function failed(string $message): self
    {
        return new self(success: false, message: $message, errorCode: 'ERROR');
    }
}
