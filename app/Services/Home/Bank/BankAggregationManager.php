<?php

namespace App\Services\Home\Bank;

use App\Contracts\BankAggregationProvider;
use App\Models\Home\HomeBankConnection;
use App\Models\Home\HomeExternalAccount;
use Carbon\Carbon;

class BankAggregationManager
{
    private array $providers = [];

    public function addProvider(string $name, BankAggregationProvider $provider): void
    {
        $this->providers[$name] = $provider;
    }

    public function driver(?string $name = null): BankAggregationProvider
    {
        $name ??= config('services.bank_aggregation.driver');

        if (!isset($this->providers[$name])) {
            throw new \InvalidArgumentException("Bank aggregation provider [{$name}] is not registered.");
        }

        return $this->providers[$name];
    }

    public function syncAccounts(HomeBankConnection $connection): void
    {
        $this->driver($connection->provider)->syncAccounts($connection);
    }

    public function syncTransactions(HomeExternalAccount $account, ?Carbon $since = null): void
    {
        $this->driver($account->connection->provider)->syncTransactions($account, $since);
    }

    public function refreshConnection(HomeBankConnection $connection): void
    {
        $this->driver($connection->provider)->refreshConnection($connection);
    }

    public function revokeConnection(HomeBankConnection $connection): void
    {
        $this->driver($connection->provider)->revokeConnection($connection);
    }
}
