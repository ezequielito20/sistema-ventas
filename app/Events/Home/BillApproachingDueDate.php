<?php

namespace App\Events\Home;

use App\Models\Home\HomeServiceBill;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillApproachingDueDate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HomeServiceBill $bill,
        public int $daysRemaining,
    ) {}
}
