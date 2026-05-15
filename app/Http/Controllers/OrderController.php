<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Orders\OrderAdminService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.index');
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['items.product', 'paymentMethod', 'deliveryMethod', 'deliveryZone', 'deliverySlot', 'processedBy']);

        return view('admin.orders.show', compact('order'));
    }

    public function markPaid(Order $order, OrderAdminService $admin): RedirectResponse
    {
        $this->authorize('update', $order);
        $admin->markPaid($order, Auth::user());

        return back()->with('success', 'Pago registrado.');
    }

    public function markDelivered(Order $order, OrderAdminService $admin): RedirectResponse
    {
        $this->authorize('update', $order);
        $admin->markDelivered($order, Auth::user());

        return back()->with('success', 'Entrega registrada.');
    }

    public function cancel(Order $order, OrderAdminService $admin): RedirectResponse
    {
        $this->authorize('cancel', $order);
        $admin->cancel($order, Auth::user());

        return redirect()->route('admin.orders.index')->with('success', 'Pedido cancelado.');
    }

    public function regenerateSummary(Order $order, OrderAdminService $admin): RedirectResponse
    {
        $this->authorize('update', $order);
        $admin->regenerateSummaryLink($order, Auth::user());

        return back()->with('success', 'Se generó un nuevo enlace de resumen.');
    }

    public function pdf(Order $order): Response
    {
        $this->authorize('view', $order);
        $order->load(['company', 'items', 'paymentMethod', 'deliveryMethod', 'deliveryZone', 'deliverySlot']);

        $pdf = Pdf::loadView('pdf.orders.summary', [
            'order' => $order,
            'company' => $order->company,
            'emittedAt' => now(),
        ]);

        return $pdf->download('pedido-'.$order->id.'.pdf');
    }
}
