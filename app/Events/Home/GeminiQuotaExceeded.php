<?php

namespace App\Events\Home;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeminiQuotaExceeded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $companyId,
    ) {}
}
