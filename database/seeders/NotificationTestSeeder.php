<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear notificaciones de prueba
        Notification::create([
            'user_id' => 1,
            'type' => 'new_order',
            'title' => 'Nuevo Pedido Recibido',
            'message' => 'Nuevo pedido de Cliente Prueba: Producto Test x2',
            'data' => [
                'order_id' => 1,
                'customer_name' => 'Cliente Prueba',
                'customer_phone' => '123456789',
                'product_name' => 'Producto Test',
                'quantity' => 2,
                'total_price' => 50.00,
                'notes' => 'Pedido de prueba para verificar notificaciones',
            ],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => 1,
            'type' => 'new_order',
            'title' => 'Pedido Urgente',
            'message' => 'Pedido urgente de María García: Laptop Gaming x1',
            'data' => [
                'order_id' => 2,
                'customer_name' => 'María García',
                'customer_phone' => '987654321',
                'product_name' => 'Laptop Gaming',
                'quantity' => 1,
                'total_price' => 1200.00,
                'notes' => 'Necesita entrega inmediata',
            ],
            'is_read' => false,
        ]);

        $this->command->info('Notificaciones de prueba creadas exitosamente.');
    }
}
