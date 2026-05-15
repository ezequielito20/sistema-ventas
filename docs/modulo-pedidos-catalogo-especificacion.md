# Especificación: módulo de pedidos desde catálogo público/privado

| Campo | Valor |
| --- | --- |
| Versión | 1.0 |
| Fecha | 2026-05-14 |
| Stack | Laravel 13, Livewire 4, Alpine.js, Tailwind, PostgreSQL/SQLite (tests), barryvdh/laravel-dompdf |

## 1. Objetivo

Permitir que un cliente arme un **carrito persistente** desde el catálogo digital de la empresa (multi-tenant), confirme el pedido con datos de contacto y opciones de **pago** y **entrega** configurables, y que el equipo interno reciba **notificaciones por usuario** en el panel admin actual (sin Filament), gestione el pedido hasta **pagado y entregado**, y comparta un **resumen** (vista + PDF) con montos en **USD y Bs** usando la **tasa vigente congelada** al momento del pedido.

## 2. Catálogo: público, privado y carrito

### 2.1 Visibilidad

- **Público** (`companies.catalog_is_public = true`): cualquier visitante puede abrir `/{slug}` y `/{slug}/producto/{id}`.
- **Privado** (`catalog_is_public = false`): las mismas rutas solo son accesibles con **URL firmada temporal** (`hasValidSignature()`), generada desde ajustes de empresa. Sin firma válida: 403 o 404 según política unificada con el catálogo actual (recomendado: 404 para no filtrar existencia).

### 2.2 Productos mostrados

- Solo productos con `include_in_catalog = true` y stock `> 0` (regla ya alineada con `Product::scopeVisibleInPublicCatalog`).
- **No** reconsultar stock en cada tick de UI; **sí** validar stock al **confirmar pedido** (checkout). Si un ítem se agotó durante el proceso, informar y permitir quitar línea o volver al catálogo.

### 2.3 Carrito persistente

- Identificador de carrito **UUID** persistido en cookie (nombre acoplado a empresa, p. ej. prefijo + `company_id` o slug) y fila en tabla `carts` + `cart_items`.
- Debe sobrevivir recarga y cierre de navegador dentro del TTL del carrito (configurable; por defecto 30 días de inactividad o similar).
- Operaciones: añadir/actualizar cantidad, eliminar línea, listar.

## 3. Pedido (cabecera + ítems)

### 3.1 Tabla `orders` (cabecera, refactor legacy)

- Una fila por pedido; **no** una fila por producto.
- Campos conceptuales: `company_id`, datos cliente (`customer_name`, `customer_phone`), `status` (`pending` \| `processed` \| `cancelled`), `paid_at`, `delivered_at` (ambos nullables).
- **Cierre automático**: cuando `paid_at` y `delivered_at` están definidos, `status` pasa a `processed` (en el mismo flujo de guardado).
- Referencias a `company_payment_method_id`, `company_delivery_method_id`, `delivery_zone_id` (nullable si no aplica), `delivery_slot_id` (nullable si la empresa no exige slot en v1).
- Montos congelados: `exchange_rate_used`, subtotales y totales en USD/Bs, costo de envío en USD, descuento por método de pago (% snapshot + importe).
- Resumen público: `public_summary_token` (único), `public_summary_expires_at`.
- Notas opcionales del cliente.
- `customer_id`, `sale_id`, `processed_by`, `processed_at`: opcionales para evolución futura (venta formal); el flujo v1 no exige crear venta al marcar pagado/entregado.

### 3.2 Tabla `order_items`

- `order_id`, `product_id`, `product_name` (snapshot), `quantity`, `unit_price_usd`, `line_total_usd` (post-reparto de descuento de pago si aplica), montos en Bs derivados o solo a nivel cabecera según implementación (mínimo: totales en cabecera + líneas USD con tasa para PDF).

## 4. Métodos de pago (tenant)

Tabla `company_payment_methods`:

- `company_id`, nombre, texto de **instrucciones/datos** para el cliente (pago móvil, etc.), `discount_percent` (0–100, 0 = sin descuento), `sort_order`, `is_active`.

Reglas:

- Si el cliente elige un método con descuento, el **% se aplica sobre el subtotal de productos (USD)** antes del costo de envío; el reparto por línea es proporcional o solo total en cabecera (documentar en código el criterio elegido).
- El descuento y la tasa quedan **congelados** en el pedido al confirmar.

## 5. Métodos de entrega, zonas y franjas

### 5.1 `company_delivery_methods`

- `company_id`, `type`: `pickup` \| `delivery`, nombre, texto de instrucciones, datos de retiro (dirección/horario sugerido para pickup), `sort_order`, `is_active`.

### 5.2 `delivery_zones`

- Asociadas a un método **delivery**; nombre, `extra_fee_usd`, `is_active`.

### 5.3 `delivery_slots`

- `company_id`, `company_delivery_method_id`, `delivery_zone_id` (null para pickup si aplica), `starts_at`, `ends_at`, `max_orders` (por defecto 1), `booked_count`, `is_active`.
- Al confirmar un pedido que usa slot: transacción con incremento de `booked_count` y comprobación `booked_count < max_orders`.
- Al **cancelar** un pedido `pending` que tenía slot: decrementar `booked_count`.
- Listado para cliente: solo slots futuros con capacidad disponible.

## 6. Stock

- Al **crear** el pedido (checkout confirmado): validar stock, decrementar stock de cada producto, crear ítems.
- Al **cancelar** pedido `pending`: devolver cantidades al stock.
- No decrementar dos veces al pasar a `processed`.

## 7. Notificaciones (admin actual)

- Tipo `new_order` únicamente en v1.
- **Fan-out**: una fila `notifications` por cada usuario de la misma `company_id` que tenga permiso de ver pedidos (p. ej. `orders.index`).
- Campos denormalizados recomendados: `company_id`, `order_id` para consultas y limpieza.
- **Leída por usuario**: `is_read` / `read_at` existentes por fila.
- **No** eliminar al marcar leída; **sí** eliminar todas las filas asociadas al `order_id` cuando el pedido pasa a `processed` o `cancelled`.
- Contador de no leídas en campana: notificaciones `new_order` cuyo pedido siga `pending` (o política acordada: todas no leídas del tipo).

## 8. UI admin

- **Campana** en navbar + **panel lateral derecho** (drawer) con lista y detalle breve; clic navega a detalle del pedido.
- Sidebar: entrada **Pedidos** y subentrada o rutas para **Métodos de pago** y **Métodos de entrega** (zonas/slots) bajo permisos y módulo de plan `orders`.

## 9. Resumen público y PDF

- Ruta global `GET /resumen/{token}` (token no adivinable).
- Validar `public_summary_expires_at` > now.
- Vista HTML con logo de empresa, ítems, USD y Bs, método de pago y entrega, totales.
- PDF con DomPDF reutilizando los mismos datos congelados.

## 10. Tasa de cambio

- Fuente v1: último registro en `exchange_rates` (`ExchangeRate::current()` / `currentRecord()`), alineado con el código existente (global). Si en el futuro la tasa es por empresa, sustituir servicio de resolución sin cambiar columnas congeladas del pedido.

## 11. Permisos y plan

- Prefijos sugeridos: `orders.*`, `order-payment-methods.*`, `order-delivery-methods.*` (o nombres acordes al seeder).
- Módulo `orders` en `config/plan_modules.php` con `permission_prefixes`.
- Incluir `orders` en `features` de planes que deban usar el módulo (coherente con catálogo profesional/empresarial y pruebas locales).

## 12. Criterios de aceptación (resumen)

1. Catálogo público sin firma; privado solo con URL firmada válida.
2. Carrito persiste tras recargar y cerrar navegador (misma cookie + servidor).
3. Checkout falla con mensaje claro si stock insuficiente o slot lleno.
4. Pedido crea ítems, congela tasa y totales, decrementa stock, reserva slot, notifica a todos los usuarios elegibles.
5. Marcar `paid_at` y `delivered_at` lleva a `processed` y borra notificaciones del pedido.
6. Cancelar pedido pendiente restaura stock y libera slot y borra notificaciones.
7. Resumen y PDF coherentes con datos congelados.

## 13. Riesgos / notas

- Concurrencia en slots y stock: usar transacciones y bloqueo pesimista o `UPDATE ... WHERE booked_count < max_orders` con comprobación de filas afectadas.
- Tests SQLite: migraciones deben ser compatibles (sin asunciones solo-PostgreSQL sin alternativa).
