<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DebtPaymentController extends Controller
{
    /**
     * Eliminar un pago de deuda específico
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Buscar el pago de deuda
            $debtPayment = DebtPayment::where('company_id', Auth::user()->company_id)
                ->findOrFail($id);

            // Obtener información para el log
            $paymentInfo = [
                'id' => $debtPayment->id,
                'sale_id' => $debtPayment->sale_id,
                'payment_amount' => $debtPayment->payment_amount,
                'customer_id' => $debtPayment->customer_id
            ];

            // Actualizar la deuda del cliente
            $customer = Customer::findOrFail($debtPayment->customer_id);
            $customer->total_debt += $debtPayment->payment_amount;
            $customer->save();

            // Eliminar el pago de deuda
            $debtPayment->delete();

            DB::commit();



            return response()->json([
                'success' => true,
                'message' => '¡Pago de deuda eliminado exitosamente!',
                'icons' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            


            return response()->json([
                'success' => false,
                'message' => 'Hubo un problema al eliminar el pago de deuda: ' . $e->getMessage(),
                'icons' => 'error'
            ], 500);
        }
    }

    /**
     * Obtener pagos de deuda de una venta específica
     */
    public function getPaymentsBySale($saleId)
    {
        try {
            $payments = DebtPayment::where('sale_id', $saleId)
                ->where('company_id', Auth::user()->company_id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los pagos de deuda'
            ], 500);
        }
    }

    /**
     * Eliminar todos los pagos de deuda de una venta específica
     */
    public function deletePaymentsBySale($saleId)
    {
        try {
            DB::beginTransaction();

            // Verificar que la venta existe y pertenece a la compañía
            $sale = Sale::where('company_id', Auth::user()->company_id)
                ->findOrFail($saleId);

            // Obtener todos los pagos de la venta
            $payments = DebtPayment::where('sale_id', $saleId)
                ->where('company_id', Auth::user()->company_id)
                ->get();

            if ($payments->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay pagos de deuda asociados a esta venta',
                    'icons' => 'info'
                ], 404);
            }

            $totalAmount = $payments->sum('payment_amount');
            $paymentsCount = $payments->count();

            // Actualizar la deuda del cliente
            $customer = Customer::findOrFail($sale->customer_id);
            $customer->total_debt += $totalAmount;
            $customer->save();

            // Eliminar todos los pagos
            $payments->each(function ($payment) {
                $payment->delete();
            });

            DB::commit();



            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$paymentsCount} pagos de deuda por un total de $" . number_format($totalAmount, 2),
                'icons' => 'success',
                'deleted_count' => $paymentsCount,
                'total_amount' => $totalAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            


            return response()->json([
                'success' => false,
                'message' => 'Hubo un problema al eliminar los pagos de deuda: ' . $e->getMessage(),
                'icons' => 'error'
            ], 500);
        }
    }
} 