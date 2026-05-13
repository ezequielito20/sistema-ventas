<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Services\GeminiOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $record = ExchangeRate::currentRecord();

        return view('admin.v2.scanner.index', [
            'initialRate' => $record ? (float) $record->rate : 134.0,
            'rateUpdatedAt' => $record ? $record->updated_at->format('d/m/Y H:i') : null,
        ]);
    }

    public function ocr(Request $request, GeminiOcrService $gemini): JsonResponse
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        $imageBase64 = $request->input('image');

        if (str_starts_with($imageBase64, 'data:image/')) {
            $imageBase64 = substr($imageBase64, strpos($imageBase64, 'base64,') + 7);
        }

        $value = $gemini->extractNumber($imageBase64);

        if ($value === null) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo detectar un número en la imagen.',
            ]);
        }

        return response()->json([
            'success' => true,
            'value' => $value,
            'engine' => 'gemini',
        ]);
    }
}
