<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class OrderSummaryController extends Controller
{
    public function show(string $token): View
    {
        $order = Order::query()
            ->where('public_summary_token', $token)
            ->with(['company', 'items', 'paymentMethod', 'deliveryMethod', 'deliveryZone', 'deliverySlot'])
            ->firstOrFail();

        if ($order->public_summary_expires_at && $order->public_summary_expires_at->isPast()) {
            abort(410, 'El enlace de resumen ha expirado.');
        }

        return view('public.order-summary', [
            'order' => $order,
        ]);
    }

    public function pdf(string $token)
    {
        $order = Order::query()
            ->where('public_summary_token', $token)
            ->with(['company', 'items', 'paymentMethod', 'deliveryMethod', 'deliveryZone', 'deliverySlot'])
            ->firstOrFail();

        if ($order->public_summary_expires_at && $order->public_summary_expires_at->isPast()) {
            abort(410);
        }

        $pdf = Pdf::loadView('pdf.orders.summary', [
            'order' => $order,
            'emittedAt' => now(),
        ]);

        return $pdf->download('resumen-pedido-'.$order->id.'.pdf');
    }
}
