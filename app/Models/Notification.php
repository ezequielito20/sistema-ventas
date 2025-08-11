<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for recent notifications.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get formatted created date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Get time ago format.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification icon based on type.
     */
    public function getIconAttribute(): string
    {
        return [
            'new_order' => 'fas fa-shopping-cart',
            'order_processed' => 'fas fa-check-circle',
            'order_cancelled' => 'fas fa-times-circle',
        ][$this->type] ?? 'fas fa-bell';
    }

    /**
     * Get notification color based on type.
     */
    public function getColorAttribute(): string
    {
        return [
            'new_order' => 'primary',
            'order_processed' => 'success',
            'order_cancelled' => 'danger',
        ][$this->type] ?? 'info';
    }

    /**
     * Create a new order notification.
     */
    public static function createOrderNotification(Order $order): self
    {
        return self::create([
            'user_id' => 1, // Admin user ID (company_id = 1)
            'type' => 'new_order',
            'title' => 'Nuevo Pedido Recibido',
            'message' => "Nuevo pedido de {$order->customer_name}: {$order->product->name} x{$order->quantity}",
            'data' => [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'product_name' => $order->product->name,
                'quantity' => $order->quantity,
                'total' => $order->total_price,
                'notes' => $order->notes,
            ],
        ]);
    }
}
