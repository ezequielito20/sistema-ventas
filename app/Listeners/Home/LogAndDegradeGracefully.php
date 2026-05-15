<?php

namespace App\Listeners\Home;

use App\Events\Home\GeminiQuotaExceeded;
use Illuminate\Support\Facades\Log;

class LogAndDegradeGracefully
{
    public function handle(GeminiQuotaExceeded $event): void
    {
        Log::channel('home')->warning('home.ai.quota_exceeded', [
            'company_id' => $event->companyId,
            'limit' => config('home.daily_ai_limit'),
        ]);
    }
}
