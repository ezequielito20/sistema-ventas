<?php

namespace App\Jobs\Home;

use App\Contracts\BankAggregationProvider;
use App\Events\Home\BankSyncFailed;
use App\Models\Home\HomeExternalAccount;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBankTransactionsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public int $tries = 3;

    public function __construct(
        public HomeExternalAccount $account,
        public ?Carbon $since = null,
    ) {}

    public function uniqueId(): string
    {
        return 'sync-bank-txn-' . $this->account->id;
    }

    public function handle(BankAggregationProvider $provider): void
    {
        try {
            $provider->syncTransactions(
                $this->account,
                $this->since ?? $this->account->last_synced_at,
            );

            $this->account->update([
                'last_synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            if ($this->attempts() < $this->tries) {
                $this->release(30);
                return;
            }

            BankSyncFailed::dispatch(
                $this->account->connection,
                $e->getMessage(),
            );
        }
    }
}
