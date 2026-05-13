<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiOcrService
{
    public function extractNumber(string $imageBase64): ?float
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            Log::error('Gemini OCR: API key not configured');
            return null;
        }

        $model = config('services.gemini.model', 'gemini-2.0-flash');

        $response = Http::timeout(10)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [
                        ['text' => 'You are a price OCR. Extract ONLY the numeric price value from the image.
Rules:
- Return ONLY the number with decimal point (e.g. 1.25, 500.00)
- If "$" or "Bs" appears, ignore the currency symbol
- If "," is a decimal separator, convert to "." (e.g. 1,25 → 1.25)
- If no number is found, return exactly: null
- Do NOT return any other text, explanation, or formatting
- Examples: "$1.25" → 1.25, "Bs 625,00" → 625.00, "500" → 500.00'],
                        ['inline_data' => [
                            'mime_type' => 'image/png',
                            'data' => $imageBase64,
                        ]],
                    ],
                ]],
                'generationConfig' => [
                    'temperature' => 0.0,
                    'maxOutputTokens' => 10,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini OCR API error: ' . $response->body());
            return null;
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null || trim($text) === 'null' || trim($text) === '') {
            return null;
        }

        $cleaned = trim($text);
        $cleaned = preg_replace('/[^0-9.,]/', '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);

        $dots = substr_count($cleaned, '.');
        if ($dots > 1) {
            $lastDot = strrpos($cleaned, '.');
            $cleaned = str_replace('.', '', substr($cleaned, 0, $lastDot)) . substr($cleaned, $lastDot);
        }

        $value = floatval($cleaned);
        return $value > 0 ? $value : null;
    }
}
