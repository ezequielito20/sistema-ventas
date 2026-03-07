<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'exchange-rate:update {--force : Forzar actualización aunque ya se haya actualizado hoy}';

    /**
     * The console command description.
     */
    protected $description = 'Actualiza la tasa de cambio BCV desde ve.dolarapi.com automáticamente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Consultando tasa BCV desde ve.dolarapi.com...');

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://ve.dolarapi.com/v1/dolares/oficial');

            if (!$response->successful()) {
                $this->error('❌ Error al consultar la API: HTTP ' . $response->status());
                Log::error('UpdateExchangeRate: API respondió con código ' . $response->status());
                return self::FAILURE;
            }

            $data = $response->json();

            // La API retorna: { "nombre": "BCV", "promedio": 91.03, ... }
            $rate = null;

            if (isset($data['promedio']) && is_numeric($data['promedio'])) {
                $rate = (float) $data['promedio'];
            } elseif (isset($data['price']) && is_numeric($data['price'])) {
                $rate = (float) $data['price'];
            }

            if (!$rate || $rate <= 0) {
                $this->error('❌ La respuesta de la API no contiene una tasa válida.');
                Log::error('UpdateExchangeRate: Respuesta inválida de la API', ['data' => $data]);
                return self::FAILURE;
            }

            // Actualizar o crear el registro global (siempre id=1)
            $record = ExchangeRate::first();

            if ($record) {
                $record->update([
                    'rate'       => $rate,
                    'source'     => 'auto_bcv',
                    'fetched_at' => now(),
                ]);
            } else {
                ExchangeRate::create([
                    'rate'          => $rate,
                    'source'        => 'auto_bcv',
                    'currency_pair' => 'USD/VES',
                    'fetched_at'    => now(),
                ]);
            }

            $this->info("✅ Tasa BCV actualizada exitosamente: {$rate} Bs/USD");
            Log::info("UpdateExchangeRate: Tasa actualizada a {$rate} Bs/USD (source: auto_bcv)");

            return self::SUCCESS;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->error('❌ No se pudo conectar con ve.dolarapi.com: ' . $e->getMessage());
            Log::error('UpdateExchangeRate: Error de conexión', ['error' => $e->getMessage()]);
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('❌ Error inesperado: ' . $e->getMessage());
            Log::error('UpdateExchangeRate: Error inesperado', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }
    }
}
