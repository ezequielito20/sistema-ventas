<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    /**
     * Marcar un pago como realizado.
     */
    public function markAsPaid(SubscriptionPayment $payment, int $processedBy, ?UploadedFile $receipt = null): void
    {
        $data = [
            'status' => 'paid',
            'paid_at' => now(),
            'processed_by' => $processedBy,
        ];

        if ($receipt) {
            $data['proof_of_payment'] = $this->uploadReceipt($payment, $receipt);
        }

        $payment->update($data);

        // Si hay una suscripción suspendida, reactivarla
        $subscription = $payment->subscription;
        if ($subscription && $subscription->isSuspended()) {
            app(SubscriptionService::class)->activate($subscription);
        }
    }

    /**
     * Subir comprobante de pago.
     */
    public function uploadReceipt(SubscriptionPayment $payment, UploadedFile $file): string
    {
        $disk = ImageUrlService::getStorageDisk();
        $folder = 'subscription_receipts/' . $payment->company_id;

        return $file->store($folder, $disk);
    }

    /**
     * Obtener URL del comprobante.
     */
    public function getReceiptUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return ImageUrlService::getImageUrl($path);
    }

    /**
     * Cancelar un pago.
     */
    public function cancelPayment(SubscriptionPayment $payment): void
    {
        $payment->update(['status' => 'cancelled']);
    }

    /**
     * Marcar pagos vencidos como overdue.
     */
    public function markOverduePayments(): int
    {
        return SubscriptionPayment::where('status', 'pending')
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => 'overdue']);
    }

    /**
     * Obtener pagos por rango de fechas con filtros.
     */
    public function getPaymentsWithFilters(array $filters = [])
    {
        $query = SubscriptionPayment::with(['company:id,name,nit', 'subscription.plan:id,name']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('nit', 'ILIKE', '%' . $search . '%');
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('due_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('due_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('due_date', 'desc');
    }

    /**
     * Estadísticas de pagos.
     */
    public function paymentStats(): array
    {
        $pending = SubscriptionPayment::where('status', 'pending')->count();
        $paid = SubscriptionPayment::where('status', 'paid')->count();
        $overdue = SubscriptionPayment::where('status', 'overdue')->count();
        $totalPending = (float) SubscriptionPayment::whereIn('status', ['pending', 'overdue'])->sum('amount');

        return [
            'pending_count' => $pending,
            'paid_count' => $paid,
            'overdue_count' => $overdue,
            'total_pending_amount' => $totalPending,
        ];
    }
}
