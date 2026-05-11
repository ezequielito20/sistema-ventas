<div class="space-y-6">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Clientes (Empresas)</h1>
                <p class="ui-panel__subtitle">Gestión de todas las empresas que usan el sistema.</p>
            </div>
            <div>
                <a href="{{ route('super-admin.companies.create') }}" class="ui-btn ui-btn-primary text-sm" wire:navigate>
                    <i class="fas fa-plus"></i> Nuevo cliente
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
        <x-ui.stat-card variant="info" icon="fas fa-building" trend="Total" label="Empresas" :value="number_format($stats['total'])" meta="Registradas" />
        <x-ui.stat-card variant="success" icon="fas fa-check-circle" trend="Activas" label="Activas" :value="number_format($stats['active'])" meta="Con servicio" />
        <x-ui.stat-card variant="warning" icon="fas fa-clock" trend="Trial" label="En trial" :value="number_format($stats['trial'])" meta="Período prueba" />
        <x-ui.stat-card variant="danger" icon="fas fa-ban" trend="Suspendidas" label="Suspendidas" :value="number_format($stats['suspended'])" meta="Sin servicio" />
    </div>

    {{-- Filtros --}}
    <div class="ui-panel" x-data="{ showFilters: false }">
        <div class="ui-panel__header flex items-center justify-between gap-3">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Búsqueda y segmentación de empresas.</p>
            </div>
            <button type="button" class="ui-btn ui-btn-ghost text-sm" @click="showFilters = !showFilters">
                <i class="fas" :class="showFilters ? 'fa-sliders-h' : 'fa-filter'"></i>
                <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros avanzados'"></span>
            </button>
        </div>
        <div class="ui-panel__body space-y-4" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end xl:grid-cols-[10rem_10rem_10rem_10rem_auto]">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Estado</label>
                    <select wire:model.live="statusFilter" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        <option value="">Todos</option>
                        <option value="active">Activas</option>
                        <option value="trial">Trial</option>
                        <option value="suspended">Suspendidas</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Plan</label>
                    <select wire:model.live="planFilter" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        <option value="">Todos</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Desde</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Hasta</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                </div>
                <div>
                    <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost w-full text-sm">
                        <i class="fas fa-eraser"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="shrink-0">
                    <h2 class="ui-panel__title">Listado</h2>
                    <p class="ui-panel__subtitle">{{ $companies->total() }} resultado(s) · Página {{ $companies->currentPage() }} de {{ $companies->lastPage() }}</p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                    <div class="relative min-w-[16rem] flex-1 lg:min-w-[18rem]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar nombre, NIT o email..." class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost text-sm" title="Limpiar">
                            <i class="fas fa-eraser"></i>
                        </button>
                        @if (!$companies->isEmpty())
                            <button type="button" wire:click="toggleSelectionMode" class="ui-btn {{ $selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost' }} text-sm">
                                <i class="fas {{ $selectionMode ? 'fa-times-circle' : 'fa-check-square' }}"></i>
                                {{ $selectionMode ? 'Cancelar' : 'Seleccionar' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="ui-panel__body p-0">
            @if ($companies->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-400">No hay empresas que coincidan con los filtros.</p>
            @else
                @if ($selectionMode)
                    <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">{{ count($selectedIds) }} empresa(s) seleccionada(s)</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="toggleSelectAll" class="ui-btn ui-btn-ghost text-sm">
                                <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                                {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                            </button>
                            <button type="button" wire:click="openBulkDeleteModal" class="ui-btn ui-btn-danger text-sm" @disabled(count($selectedIds) === 0)>
                                <i class="fas fa-trash-alt"></i> Eliminar seleccionadas
                            </button>
                        </div>
                    </div>
                @endif

                <div class="ui-table-wrap border-0 rounded-none">
                    <table class="ui-table ui-table--nowrap-actions">
                        <thead>
                            <tr>
                                @if ($selectionMode)
                                    <th class="w-12 text-center"><input type="checkbox" @checked($allCurrentPageSelected) wire:click="toggleSelectAll" class="rounded border-slate-500 bg-slate-900" /></th>
                                @endif
                                <th>Empresa</th>
                                <th>NIT</th>
                                <th>Plan</th>
                                <th class="text-center">Estado</th>
                                <th>Fecha Cobro</th>
                                <th>Último Pago</th>
                                <th class="text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($companies as $company)
                                <tr wire:key="company-row-{{ $company->id }}">
                                    @if ($selectionMode)
                                        <td class="text-center">
                                            <input type="checkbox" value="{{ $company->id }}" @checked(in_array($company->id, $selectedIds, true)) wire:click="toggleSelection({{ $company->id }})" class="rounded border-slate-500 bg-slate-900" />
                                        </td>
                                    @endif
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/15 text-sm font-semibold uppercase text-cyan-200">
                                                {{ mb_substr($company->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-white">{{ $company->name }}</p>
                                                <p class="text-xs text-slate-400">{{ $company->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm text-slate-300">{{ $company->nit ?? '—' }}</td>
                                    <td class="text-sm text-slate-300">{{ $company->subscription?->plan?->name ?? 'Sin plan' }}</td>
                                    <td class="text-center">
                                        @if ($company->subscription_status === 'active')
                                            <span class="ui-badge ui-badge-success">Activo</span>
                                        @elseif ($company->subscription_status === 'trial')
                                            <span class="ui-badge ui-badge-info">Trial</span>
                                        @elseif ($company->subscription_status === 'suspended')
                                            <span class="ui-badge ui-badge-danger">Suspendido</span>
                                        @else
                                            <span class="ui-badge ui-badge-warning">{{ $company->subscription_status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-slate-300">{{ $company->subscription?->next_billing_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-sm text-slate-300">
                                        @if ($company->subscription?->latestPayment?->paid_at)
                                            <span class="text-emerald-400">$ {{ number_format($company->subscription->latestPayment->amount, 2) }}</span>
                                            <br><span class="text-xs text-slate-500">{{ $company->subscription->latestPayment->paid_at->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-slate-500">Sin pagos</span>
                                        @endif
                                    </td>
                                    <td class="text-left">
                                        <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                            <button type="button" wire:click="openDetailModal({{ $company->id }})" class="ui-icon-action ui-icon-action--info" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('super-admin.companies.show', $company->id) }}" class="ui-icon-action ui-icon-action--primary" title="Gestionar">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                            <button type="button" wire:click="openDeleteModal({{ $company->id }})" class="ui-icon-action ui-icon-action--danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="ui-panel__body border-t border-slate-700/50">
            <x-ui.pagination :paginator="$companies" scrollIntoView=".ui-panel" />
        </div>
    </div>

    {{-- Modal de Detalle --}}
    @if ($showDetailModal && $detailCompany)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeDetailModal"></div>
            <div class="relative w-full max-w-2xl mx-4 my-8" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header flex items-center justify-between">
                        <h3 class="ui-panel__title">{{ $detailCompany['name'] }}</h3>
                        <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="ui-panel__body space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><span class="text-xs text-slate-400">NIT</span><p class="text-sm text-slate-200">{{ $detailCompany['nit'] }}</p></div>
                            <div><span class="text-xs text-slate-400">Email</span><p class="text-sm text-slate-200">{{ $detailCompany['email'] }}</p></div>
                            <div><span class="text-xs text-slate-400">Teléfono</span><p class="text-sm text-slate-200">{{ $detailCompany['phone'] ?: '—' }}</p></div>
                            <div><span class="text-xs text-slate-400">Tipo Negocio</span><p class="text-sm text-slate-200">{{ $detailCompany['business_type'] ?: '—' }}</p></div>
                            <div><span class="text-xs text-slate-400">Plan</span><p class="text-sm text-slate-200">{{ $detailCompany['plan_name'] }}</p></div>
                            <div><span class="text-xs text-slate-400">Estado</span><p class="text-sm text-slate-200">{{ $detailCompany['subscription_status'] }}</p></div>
                            <div><span class="text-xs text-slate-400">Día Cobro</span><p class="text-sm text-slate-200">Día {{ $detailCompany['billing_day'] }}</p></div>
                            <div><span class="text-xs text-slate-400">Próx. Cobro</span><p class="text-sm text-slate-200">{{ $detailCompany['next_billing_date'] }}</p></div>
                            <div><span class="text-xs text-slate-400">Monto Mensual</span><p class="text-sm font-semibold text-emerald-400">$ {{ number_format($detailCompany['amount'], 2) }}</p></div>
                            <div><span class="text-xs text-slate-400">Último Pago</span><p class="text-sm text-slate-200">{{ $detailCompany['last_payment_date'] }}</p></div>
                        </div>
                        <hr class="border-slate-700">
                        <div class="grid grid-cols-4 gap-4 text-center">
                            <div><p class="text-lg font-bold text-white">{{ $detailCompany['users_count'] }}</p><p class="text-xs text-slate-400">Usuarios</p></div>
                            <div><p class="text-lg font-bold text-white">{{ $detailCompany['customers_count'] }}</p><p class="text-xs text-slate-400">Clientes</p></div>
                            <div><p class="text-lg font-bold text-white">{{ $detailCompany['sales_count'] }}</p><p class="text-xs text-slate-400">Ventas</p></div>
                            <div><p class="text-lg font-bold text-white">$ {{ number_format($detailCompany['total_revenue'], 0) }}</p><p class="text-xs text-slate-400">Facturación</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Eliminación --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeDeleteModal"></div>
            <div class="relative w-full max-w-md mx-4" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header">
                        <h3 class="ui-panel__title text-rose-400">Confirmar eliminación</h3>
                    </div>
                    <div class="ui-panel__body space-y-4">
                        <p class="text-sm text-slate-300">¿Estás seguro de eliminar la empresa <strong class="text-white">{{ $deleteTargetName }}</strong>?</p>
                        <p class="text-xs text-rose-400">Esta acción es irreversible. Se eliminarán todos los usuarios y datos asociados.</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="confirmDelete" class="ui-btn ui-btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Eliminación Masiva --}}
    @if ($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeBulkDeleteModal"></div>
            <div class="relative w-full max-w-md mx-4" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header">
                        <h3 class="ui-panel__title text-rose-400">Eliminar empresas seleccionadas</h3>
                    </div>
                    <div class="ui-panel__body space-y-4">
                        <p class="text-sm text-slate-300">Vas a eliminar <strong class="text-white">{{ count($selectedIds) }} empresa(s)</strong>.</p>
                        <p class="text-xs text-rose-400">Esta acción es irreversible. Se eliminarán todos los datos asociados.</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closeBulkDeleteModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="confirmBulkDelete" class="ui-btn ui-btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
