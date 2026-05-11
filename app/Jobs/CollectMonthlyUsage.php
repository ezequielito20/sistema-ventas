<?php

namespace App\Jobs;

use App\Services\UsageCollectorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CollectMonthlyUsage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(UsageCollectorService $usageCollector): void
    {
        $logs = $usageCollector->collectForAllActiveCompanies();
    }
}
