<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ExchangeRateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Devuelve la tasa de cambio actual desde la BD.
     */
    public function current(): JsonResponse
    {
        $record = ExchangeRate::currentRecord();

        return response()->json([
            'success'    => true,
            'rate'       => $record ? (float) $record->rate : 134.0,
            'source'     => $record ? $record->source : 'manual',
            'updated_at' => $record ? $record->updated_at->format('d/m/Y H:i') : null,
            'fetched_at' => $record && $record->fetched_at
                ? $record->fetched_at->format('d/m/Y H:i')
                : null,
        ]);
    }

    /**
     * Dispara una actualización manual desde la API BCV.
     * Llama al mismo Command del scheduler.
     */
    public function forceUpdate(): JsonResponse
    {
        try {
            // Ejecutar el command y capturar el resultado
            $exitCode = Artisan::call('exchange-rate:update', ['--force' => true]);

            if ($exitCode === 0) {
                $record = ExchangeRate::currentRecord();
                return response()->json([
                    'success'    => true,
                    'message'    => 'Tasa BCV actualizada exitosamente desde ve.dolarapi.com',
                    'rate'       => $record ? (float) $record->rate : 134.0,
                    'source'     => 'auto_bcv',
                    'updated_at' => $record ? $record->updated_at->format('d/m/Y H:i') : null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener la tasa desde ve.dolarapi.com. Verifique la conexión o inténtelo más tarde.',
            ], 422);
        } catch (\Exception $e) {
            Log::error('ExchangeRateController::forceUpdate error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar la tasa de cambio.',
            ], 500);
        }
    }
}
