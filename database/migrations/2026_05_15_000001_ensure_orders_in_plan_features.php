<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Planes con lista explícita de features que no incluía «orders» dejaban sin menú
     * pedidos, notificaciones y checkout (solo se veía «Mi plan» en permisos / sidebar).
     */
    public function up(): void
    {
        $plans = DB::table('plans')->select(['id', 'features'])->get();
        foreach ($plans as $row) {
            $decoded = json_decode($row->features ?? 'null', true);
            if (! is_array($decoded) || $decoded === []) {
                continue;
            }
            if (in_array('orders', $decoded, true)) {
                continue;
            }
            $decoded[] = 'orders';
            DB::table('plans')->where('id', $row->id)->update([
                'features' => json_encode(array_values(array_unique($decoded))),
            ]);
        }
    }

    public function down(): void
    {
        // No revertimos: quitar «orders» podría romper contratos ya asumidos tras el deploy.
    }
};
