<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySlot extends Model
{
    protected $fillable = [
        'company_id',
        'company_delivery_method_id',
        'delivery_zone_id',
        'weekday_iso',
        'delivery_time',
        'max_orders',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weekday_iso' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function isoWeekdaysLabelsEs(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
    }

    public function weekdayLabelEs(): string
    {
        return self::isoWeekdaysLabelsEs()[(int) $this->weekday_iso] ?? '—';
    }

    /** Hora HH:MM (columna puede venir como H:i:s). */
    public function timeShort(): string
    {
        $t = (string) $this->delivery_time;
        if ($t === '') {
            return '—';
        }

        return strlen($t) >= 8 ? substr($t, 0, 5) : $t;
    }

    /**
     * Próxima fecha calendario (sin hora) en la que aplicaría esta franja recurrente,
     * considerando día ISO y si la hora de ese día ya pasó respecto de $now.
     */
    public function resolveNextScheduledDeliveryDate(?Carbon $now = null): Carbon
    {
        $tz = config('app.timezone');
        $now = ($now ?: Carbon::now($tz))->copy()->timezone($tz);
        $today = $now->copy()->startOfDay();

        $timeSql = trim((string) $this->delivery_time);
        if (strlen($timeSql) <= 5) {
            $timeSql .= strlen($timeSql) === 5 ? ':00' : ':00:00';
        }

        $targetW = (int) $this->weekday_iso;

        for ($attempt = 0; $attempt < 21; $attempt++) {
            $candidateDay = $today->copy()->addDays($attempt);
            if ((int) $candidateDay->isoWeekday() !== $targetW) {
                continue;
            }
            $combined = Carbon::parse($candidateDay->format('Y-m-d').' '.$timeSql, $tz);
            if ($combined->greaterThan($now)) {
                return $candidateDay;
            }
        }

        $c = $today->copy()->addDays(14);
        while ((int) $c->isoWeekday() !== $targetW) {
            $c->addDay();
        }

        return $c;
    }

    /** Pedidos pendientes que consumen cupo ese día para esta franja. */
    public function pendingBookingsForDate(string $ymd): int
    {
        return Order::query()
            ->where('delivery_slot_id', $this->id)
            ->whereDate('scheduled_delivery_date', $ymd)
            ->where('status', 'pending')
            ->count();
    }

    public function hasCapacity(?Carbon $now = null): bool
    {
        $d = $this->resolveNextScheduledDeliveryDate($now)->format('Y-m-d');

        return $this->pendingBookingsForDate($d) < (int) $this->max_orders;
    }

    public function bookingsNextOccurrence(): int
    {
        return $this->pendingBookingsForDate(
            $this->resolveNextScheduledDeliveryDate()->format('Y-m-d')
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(CompanyDeliveryMethod::class, 'company_delivery_method_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }
}
