<?php

namespace App\Jobs\Home;

use App\Contracts\ProductIdentificationResult;
use App\Services\Home\HomeVisionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessGeminiVisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(
        public string $imageBase64,
        public string $mimeType,
    ) {}

    public function handle(HomeVisionService $vision): ProductIdentificationResult
    {
        return $vision->identifyProductFromImage($this->imageBase64, $this->mimeType);
    }
}
