<?php

namespace App\Events\Home;

use App\Models\Home\HomeProduct;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductConsumed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HomeProduct $product,
        public int $quantity,
        public string $method,
    ) {}
}
