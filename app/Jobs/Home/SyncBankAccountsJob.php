<?php

namespace App\Jobs\Home;

use App\Contracts\BankAggregationProvider;
use App\Models\Home\HomeBankConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBankAccountsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public int $tries = 3;

    public function __construct(
        public HomeBankConnection $connection,
    ) {}

    public function uniqueId(): string
    {
        return 'sync-bank-accounts-' . $this->connection->id;
    }

    public function handle(BankAggregationProvider $provider): void
    {
        $this->connection->update(['status' => 'active']);

        $provider->syncAccounts($this->connection);

        $this->connection->update([
            'last_successful_sync_at' => now(),
            'last_error_message' => null,
        ]);
    }
}
