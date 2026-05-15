<?php

namespace App\Services\Home;

use App\Contracts\ProductIdentificationResult;
use App\Events\Home\GeminiQuotaExceeded;
use App\Services\GeminiOcrService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeVisionService
{
    const FALLBACK_CANDIDATES = [
        'harina' => ['Harina de Maíz', 'Harina de Trigo', 'Harina Leudante'],
        'arroz' => ['Arroz Blanco', 'Arroz Integral'],
        'aceite' => ['Aceite Vegetal', 'Aceite de Oliva'],
        'leche' => ['Leche Entera', 'Leche Descremada', 'Leche en Polvo'],
        'azucar' => ['Azúcar Blanca', 'Azúcar Morena'],
        'sal' => ['Sal Fina', 'Sal Gruesa', 'Sal Marina'],
        'cafe' => ['Café Molido', 'Café Instantáneo'],
        'pasta' => ['Pasta Larga', 'Pasta Corta'],
        'jabon' => ['Jabón de Baño', 'Jabón Líquido', 'Jabón en Polvo'],
        'shampoo' => ['Shampoo', 'Acondicionador'],
        'desodorante' => ['Desodorante Spray', 'Desodorante Barra', 'Desodorante Roll-on'],
    ];

    public function __construct(
        private readonly GeminiOcrService $gemini,
    ) {}

    public function identifyProductFromImage(string $base64, string $mimeType): ProductIdentificationResult
    {
        try {
            $response = Http::timeout(10)
                ->withOptions(['stream' => false])
                ->post(config('services.gemini.base_url') . '/v1beta/models/' . config('services.gemini.model') . ':generateContent', [
                    'contents' => [
                        'parts' => [
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                            ['text' => <<<PROMPT
Identify this household product. Return ONLY valid JSON:
{
  "name": "product name in spanish",
  "brand": "brand name or null",
  "confidence": 0.0-1.0
}
PROMPT],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $json = json_decode($text, true);

                if ($json && isset($json['name'])) {
                    return new ProductIdentificationResult(
                        name: $json['name'],
                        brand: $json['brand'] ?? null,
                        confidence: (float) ($json['confidence'] ?? 0.5),
                    );
                }
            }

            Log::channel('home')->warning('home.gemini.invalid_response', [
                'response' => substr($response->body(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            Log::channel('home')->error('home.gemini.request_failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return new ProductIdentificationResult(
            name: null,
            brand: null,
            confidence: 0.0,
        );
    }

    public function matchProductAgainstInventory(
        ProductIdentificationResult $identification,
        int $companyId,
    ): ?array {
        if (!$identification->name) {
            return null;
        }

        $products = \App\Models\Home\HomeProduct::where('company_id', $companyId)
            ->get(['id', 'name', 'brand', 'image']);

        $bestMatch = null;
        $bestScore = 0;

        foreach ($products as $product) {
            $score = $this->similarityScore($identification->name, $product->name);

            if ($identification->brand && $product->brand) {
                $brandScore = $this->similarityScore($identification->brand, $product->brand);
                $score = max($score, $brandScore * 0.8);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $product;
            }
        }

        if ($bestMatch && $bestScore >= 0.3) {
            return [
                'product' => $bestMatch,
                'score' => $bestScore,
                'is_reliable' => $bestScore >= config('home.ai_confidence_threshold', 0.75),
            ];
        }

        return null;
    }

    private function similarityScore(string $a, string $b): float
    {
        $a = mb_strtolower(trim($a));
        $b = mb_strtolower(trim($b));

        if ($a === $b) {
            return 1.0;
        }

        similar_text($a, $b, $percent);

        return $percent / 100;
    }

    public function checkDailyQuota(int $companyId): bool
    {
        $key = "home:ai:quota:{$companyId}:" . now()->toDateString();
        $count = (int) Cache::get($key, 0);

        if ($count >= config('home.daily_ai_limit', 50)) {
            GeminiQuotaExceeded::dispatch($companyId);
            return false;
        }

        Cache::increment($key);
        Cache::expire($key, now()->endOfDay()->diffInSeconds(now()));

        return true;
    }
}
