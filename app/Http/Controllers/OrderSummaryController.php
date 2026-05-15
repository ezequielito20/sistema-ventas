<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Catalog\PublicOrderCancellationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderSummaryController extends Controller
{
    public function show(string $token): View
    {
        $order = $this->resolveOrder($token);

        return view('public.order-summary', [
            'order' => $order,
            'cancelWindowMinutes' => max(1, (int) config('catalog.order_public_cancel_window_minutes', 30)),
        ]);
    }

    public function pdf(string $token)
    {
        $order = $this->resolveOrder($token);

        $pdf = Pdf::loadView('pdf.orders.summary', [
            'order' => $order,
            'company' => $order->company,
            'emittedAt' => now(),
        ]);

        return $pdf->download('resumen-pedido-'.$order->id.'.pdf');
    }

    public function cancel(Request $request, string $token, PublicOrderCancellationService $cancellation): RedirectResponse
    {
        $request->validate([
            'customer_phone' => ['required', 'string', 'max:20'],
            'confirm_cancel' => ['accepted'],
        ], [
            'confirm_cancel.accepted' => 'Debés confirmar que querés cancelar el pedido.',
        ]);

        $order = $cancellation->cancelBySummaryToken($token, (string) $request->input('customer_phone'));

        return redirect()
            ->route('order.summary.show', ['token' => $order->public_summary_token])
            ->with('order_cancelled', true);
    }

    protected function resolveOrder(string $token): Order
    {
        $order = Order::query()
            ->where('public_summary_token', $token)
            ->with(['company', 'items', 'paymentMethod', 'deliveryMethod', 'deliveryZone', 'deliverySlot'])
            ->firstOrFail();

        if ($order->public_summary_expires_at && $order->public_summary_expires_at->isPast()) {
            abort(410, 'El enlace de resumen ha expirado.');
        }

        return $order;
    }
}
