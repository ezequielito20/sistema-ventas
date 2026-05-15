<?php

namespace App\Contracts;

use App\Models\Company;
use App\Models\Home\HomeBankConnection;
use App\Models\Home\HomeExternalAccount;
use Illuminate\Http\Request;

interface BankAggregationProvider
{
    public function getAuthorizationUrl(Company $company, string $redirectUri): string;

    public function handleCallback(Request $request): BankConnectionResult;

    public function refreshConnection(HomeBankConnection $connection): void;

    public function syncAccounts(HomeBankConnection $connection): void;

    public function syncTransactions(HomeExternalAccount $account, ?\Carbon\Carbon $since): void;

    public function revokeConnection(HomeBankConnection $connection): void;
}
