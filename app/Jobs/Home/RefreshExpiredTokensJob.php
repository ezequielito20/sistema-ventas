<?php

namespace App\Jobs\Home;

use App\Contracts\BankAggregationProvider;
use App\Models\Home\HomeBankConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshExpiredTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(BankAggregationProvider $provider): void
    {
        $expired = HomeBankConnection::active()
            ->where('token_expires_at', '<=', now()->addHour())
            ->get();

        foreach ($expired as $connection) {
            try {
                $provider->refreshConnection($connection);
            } catch (\Throwable $e) {
                $connection->update([
                    'status' => 'error',
                    'last_error_message' => substr($e->getMessage(), 0, 500),
                ]);
            }
        }
    }
}
