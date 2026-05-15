<?php

namespace App\Listeners\Home;

use App\Events\Home\BankSyncFailed;
use Illuminate\Support\Facades\Log;

class LogBankSyncError
{
    public function handle(BankSyncFailed $event): void
    {
        $connection = $event->connection;

        $connection->update([
            'status' => 'error',
            'last_error_message' => substr($event->errorMessage, 0, 500),
        ]);

        Log::channel('home')->error('home.bank.sync_failed', [
            'company_id' => $connection->company_id,
            'connection_id' => $connection->id,
            'provider' => $connection->provider,
            'error' => $event->errorMessage,
        ]);
    }
}
