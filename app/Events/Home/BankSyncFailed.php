<?php

namespace App\Events\Home;

use App\Models\Home\HomeBankConnection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BankSyncFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HomeBankConnection $connection,
        public string $errorMessage,
    ) {}
}
