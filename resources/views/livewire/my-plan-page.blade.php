@php
    /** @var array{subscription: ?\App\Models\Subscription, plan: ?\App\Models\Plan, plan_is_active: bool, today: string, rows: list<array<string, mixed>>} $overview */
    $plan = $overview['plan'] ?? null;
    $subscription = $overview['subscription'] ?? null;
@endphp

<div class="my-plan-page space-y-6">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Mi plan</h1>
                <p class="ui-panel__subtitle">
                    Beneficios contratados, límites de uso y estado de tu suscripción.
                </p>
            </div>
        </div>
    </div>

    @if (! $plan)
        <div class="ui-panel">
            <div class="ui-panel__body">
                <div
                    class="rounded-xl border border-amber-500/40 bg-amber-950/35 px-4 py-4 text-sm text-amber-100"
                    role="status"
                >
                    <p class="font-semibold text-amber-50">Sin plan asignado</p>
                    <p class="mt-2 text-amber-100/90">
                        Tu empresa no tiene un plan vinculado en este momento. Si deberías tener uno, contacta al
                        administrador de la plataforma.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="ui-panel">
            <div class="ui-panel__body space-y-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-100">{{ $plan->name }}</h2>
                        @if (filled($plan->description))
                            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-400">{{ $plan->description }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($overview['plan_is_active'])
                            <span
                                class="inline-flex items-center rounded-full border border-emerald-500/40 bg-emerald-950/50 px-3 py-1 text-xs font-semibold text-emerald-200"
                            >
                                Plan activo
                            </span>
                        @else
                            <span
                                class="inline-flex items-center rounded-full border border-amber-500/40 bg-amber-950/50 px-3 py-1 text-xs font-semibold text-amber-200"
                            >
                                Plan inactivo
                            </span>
                        @endif
                        @if ($subscription)
                            <span
                                class="inline-flex items-center rounded-full border border-slate-600 bg-slate-900/80 px-3 py-1 text-xs font-medium text-slate-300"
                            >
                                Suscripción: {{ $subscription->status }}
                            </span>
                        @endif
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-slate-700/80 bg-slate-900/40 px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Precio base del plan</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-100">
                            {{ number_format((float) $plan->base_price, 2, ',', '.') }}
                        </dd>
                    </div>
                    @if ($subscription?->next_billing_date)
                        <div class="rounded-lg border border-slate-700/80 bg-slate-900/40 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Próxima facturación</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-100">
                                {{ $subscription->next_billing_date->translatedFormat('d M Y') }}
                            </dd>
                        </div>
                    @endif
                    <div class="rounded-lg border border-slate-700/80 bg-slate-900/40 px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Día de referencia (uso diario)</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-100">{{ $overview['today'] }}</dd>
                    </div>
                </dl>

                @if (! $overview['plan_is_active'])
                    <p class="text-sm text-amber-200/90">
                        El plan figura como inactivo: el acceso a módulos puede estar restringido aunque aparezcan límites
                        abajo según el contrato.
                    </p>
                @endif
            </div>
        </div>

        <div class="ui-panel">
            <div class="ui-panel__header">
                <h2 class="text-base font-semibold text-slate-100">Módulos, límites y uso</h2>
                <p class="ui-panel__subtitle mt-1">
                    Solo se listan los módulos incluidos en tu plan. «Acceso» indica si puedes usarlos ahora (plan activo).
                </p>
            </div>
            <div class="ui-panel__body overflow-x-auto p-0 sm:p-0">
                @if (count($overview['rows']) === 0)
                    <div class="px-4 py-8 text-center text-sm text-slate-400">
                        No hay módulos configurados en tu plan para mostrar aquí.
                    </div>
                @else
                    <table class="min-w-full divide-y divide-slate-800 text-left text-sm">
                        <thead class="bg-slate-900/60">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-300">Módulo</th>
                                <th class="px-4 py-3 font-semibold text-slate-300">Acceso</th>
                                <th class="px-4 py-3 font-semibold text-slate-300">Límite</th>
                                <th class="px-4 py-3 font-semibold text-slate-300">Uso / nota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach ($overview['rows'] as $row)
                                <tr class="bg-slate-950/30 hover:bg-slate-900/40">
                                    <td class="px-4 py-3 font-medium text-slate-200">{{ $row['label'] }}</td>
                                    <td class="px-4 py-3">
                                        @if ($row['effective_access'])
                                            <span class="text-emerald-400">Sí</span>
                                        @else
                                            <span class="text-slate-500">No</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-4 py-3 text-slate-300">{{ $row['limit_label'] }}</td>
                                    <td class="max-w-xs px-4 py-3 text-slate-400">{{ $row['usage_label'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif
</div>
