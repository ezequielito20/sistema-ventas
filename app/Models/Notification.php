<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'order_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    /**
     * Notificaciones de pedido nuevo cuyo pedido sigue pendiente (acción requerida).
     */
    public function scopePendingOrderAlerts(Builder $query): Builder
    {
        return $query->where('type', 'new_order')
            ->whereNotNull('order_id')
            ->whereExists(function ($q): void {
                $q->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.id', 'notifications.order_id')
                    ->where('orders.status', 'pending');
            });
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getIconAttribute(): string
    {
        return [
            'new_order' => 'fas fa-shopping-cart',
            'order_processed' => 'fas fa-check-circle',
            'order_cancelled' => 'fas fa-times-circle',
        ][$this->type] ?? 'fas fa-bell';
    }

    public function getColorAttribute(): string
    {
        return [
            'new_order' => 'primary',
            'order_processed' => 'success',
            'order_cancelled' => 'danger',
        ][$this->type] ?? 'info';
    }
}
