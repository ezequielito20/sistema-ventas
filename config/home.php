<?php

return [

    'enabled' => env('HOME_MODULE_ENABLED', true),

    'daily_ai_limit' => env('HOME_DAILY_AI_LIMIT', 50),

    'bill_alert_days' => [7, 3],

    'ai_confidence_threshold' => 0.75,

    'undo_window_seconds' => 5,

    'default_deduct_quantity' => 1,

];
