<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index()
    {
        return view('admin.orders.index');
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['product', 'customer', 'sale', 'processedBy']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Process an order (create customer and sale).
     */
    public function process(Request $request, Order $order)
    {
        $request->validate([
            'sale_date' => 'nullable|date',
        ]);

        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Este pedido ya fue procesado o cancelado.');
        }

        DB::beginTransaction();
        
        try {
            // Create or get customer
            $customer = $order->customer;
            
            if (!$customer) {
                // Create new customer
                $customer = Customer::create([
                    'name' => $order->customer_name,
                    'phone' => $order->customer_phone,
                    'company_id' => 1,
                    'total_debt' => 0,
                ]);
            }

            // Create sale
            $sale = Sale::create([
                'sale_date' => $request->sale_date ?? now(),
                'total_price' => $order->total_price,
                'company_id' => 1,
                'customer_id' => $customer->id,
                'note' => $order->notes,
            ]);

            // Create sale detail and update product stock
            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
                'unit_price' => $order->unit_price,
                'subtotal' => $order->total_price,
            ]);

            // Update product stock
            $product = Product::find($order->product_id);
            if ($product) {
                $product->stock -= $order->quantity;
                $product->save();
            }

            // Update order
            $order->update([
                'status' => 'processed',
                'customer_id' => $customer->id,
                'sale_id' => $sale->id,
                'processed_at' => now(),
                'processed_by' => Auth::id(),
            ]);

            // Update customer debt
            $customer->increment('total_debt', $order->total_price);

            // Remove the order notification since it's now processed
            Notification::where('type', 'new_order')
                ->whereJsonContains('data->order_id', $order->id)
                ->delete();

            // Notification disabled - no longer creating notifications when processing orders
            // Notification::create([
            //     'user_id' => Auth::id(),
            //     'type' => 'order_processed',
            //     'title' => 'Pedido Procesado',
            //     'message' => "Pedido #{$order->id} de {$order->customer_name} procesado exitosamente",
            //     'data' => [
            //         'order_id' => $order->id,
            //         'sale_id' => $sale->id,
            //         'customer_id' => $customer->id,
            //     ],
            // ]);

            DB::commit();

            return redirect()->route('admin.index')->with('success', 'Pedido procesado exitosamente. Se creó la venta y se actualizó la deuda del cliente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Este pedido ya fue procesado o cancelado.');
        }

        $order->update([
            'status' => 'cancelled',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        // Remove the order notification since it's now cancelled
        Notification::where('type', 'new_order')
            ->whereJsonContains('data->order_id', $order->id)
            ->delete();

        // Notification disabled - no longer creating notifications when cancelling orders
        // Notification::create([
        //     'user_id' => Auth::id(),
        //     'type' => 'order_cancelled',
        //     'title' => 'Pedido Cancelado',
        //     'message' => "Pedido #{$order->id} de {$order->customer_name} cancelado",
        //     'data' => [
        //         'order_id' => $order->id,
        //         'reason' => 'Cancelado por administrador',
        //     ],
        // ]);

        return redirect()->route('admin.index')->with('success', 'Pedido cancelado exitosamente.');
    }
}
