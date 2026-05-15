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
        'delivery_time_end',
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

    /** HH:MM (columna puede venir como H:i:s) — hora desde. */
    public function timeShort(): string
    {
        return self::formatTimeColumn((string) $this->delivery_time);
    }

    /** HH:MM — hora hasta (si falta coincide con desde por compatibilidad). */
    public function timeEndShort(): string
    {
        $end = trim((string) ($this->delivery_time_end ?? ''));
        if ($end === '') {
            return $this->timeShort();
        }

        return self::formatTimeColumn($end);
    }

    /** Texto tipo "12:00 – 17:00" o "12:00" si coincide inicio/fin. */
    public function deliveryWindowLabelShort(): string
    {
        $a = $this->timeShort();
        $b = $this->timeEndShort();
        if ($a === $b) {
            return $a;
        }

        return $a.' – '.$b;
    }

    /**
     * Próxima fecha calendario (sin hora) en la que la ventana aún puede usarse para entregar ese día respecto de $now.
     */
    public function resolveNextScheduledDeliveryDate(?Carbon $now = null): Carbon
    {
        $tz = config('app.timezone');
        $now = ($now ?: Carbon::now($tz))->copy()->timezone($tz);
        $today = $now->copy()->startOfDay();

        $startSql = self::toHmsSql((string) $this->delivery_time);
        $endRaw = trim((string) ($this->delivery_time_end ?? ''));

        $endSql = self::toHmsSql($endRaw !== '' ? $endRaw : (string) $this->delivery_time);

        $targetW = (int) $this->weekday_iso;

        for ($attempt = 0; $attempt < 21; $attempt++) {
            $candidateDay = $today->copy()->addDays($attempt);
            if ((int) $candidateDay->isoWeekday() !== $targetW) {
                continue;
            }
            $windowEnd = Carbon::parse($candidateDay->format('Y-m-d').' '.$endSql, $tz);
            if ($now->greaterThan($windowEnd)) {
                continue;
            }

            return $candidateDay;
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

    public static function formatTimeColumn(string $t): string
    {
        if ($t === '') {
            return '—';
        }

        return strlen($t) >= 8 ? substr($t, 0, 5) : $t;
    }

    /** Para parsear valores TIME desde BD o desde input HTML (HH:MM / H:M). */
    public static function toHmsSql(string $value): string
    {
        $t = trim($value);
        if ($t === '') {
            return '00:00:00';
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $t, $m)) {
            return sprintf('%02d:%02d:00', max(0, min(23, (int) $m[1])), max(0, min(59, (int) $m[2])));
        }

        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $t, $m)) {
            return sprintf('%02d:%02d:%02d', max(0, min(23, (int) $m[1])), max(0, min(59, (int) $m[2])), max(0, min(59, (int) $m[3])));
        }

        return '00:00:00';
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
