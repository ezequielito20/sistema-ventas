@if (auth()->user()->can('orders.index') || auth()->user()->isSuperAdmin())
{{--
  El header (.app-topbar) usa backdrop-filter: eso fuerza un bloque contenedor y los hijos fixed
  se anclan al header (~64px), no al viewport. Teletransportamos drawer + overlay a body.
--}}
<div x-data="adminNotifDrawer()" x-init="init()" class="relative z-50 inline-flex" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open; if(open) load()" aria-label="Notificaciones"
            class="relative rounded-md p-2 text-slate-300 hover:bg-slate-800 hover:text-white">
        <i class="fas fa-bell text-xl"></i>
        <span x-show="unread > 0" x-cloak class="absolute right-0.5 top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-0.5 text-[9px] font-bold text-white"
              x-text="unread > 9 ? '9+' : unread"></span>
    </button>
    <template x-teleport="body">
        <div>
            <div x-show="open"
                 x-cloak
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[160] bg-black/50 lg:bg-black/40"
                 @click="open = false"
                 aria-hidden="true"></div>
            <aside x-show="open"
                   x-cloak
                   role="dialog"
                   aria-modal="true"
                   aria-labelledby="admin-notifications-drawer-title"
                   x-transition:enter="transition transform duration-200 ease-out"
                   x-transition:enter-start="translate-x-full"
                   x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition transform duration-150 ease-in"
                   x-transition:leave-start="translate-x-0"
                   x-transition:leave-end="translate-x-full"
                   class="fixed inset-y-0 right-0 z-[170] flex h-[100dvh] min-h-screen w-full max-w-md flex-col border-l border-slate-700/90 bg-slate-900 shadow-2xl shadow-black/50">
                <div class="flex shrink-0 items-center justify-between border-b border-slate-700 px-4 py-3">
                    <h2 id="admin-notifications-drawer-title" class="text-sm font-semibold text-slate-100">Notificaciones</h2>
                    <button type="button" @click="open = false" class="rounded p-2 text-slate-400 hover:bg-slate-800 hover:text-slate-200" aria-label="Cerrar panel de notificaciones">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-2">
                    <template x-if="items.length === 0">
                        <p class="p-4 text-center text-sm text-slate-500">No hay notificaciones de pedidos pendientes.</p>
                    </template>
                    <template x-for="n in items" :key="n.id">
                        <a :href="n.url" @click.prevent="markReadThenGo(n)"
                           class="mb-2 block rounded-lg border border-slate-700/80 bg-slate-800/40 p-3 hover:bg-slate-800">
                            <p class="text-sm font-medium text-slate-100" x-text="n.title"></p>
                            <p class="mt-1 text-xs text-slate-400" x-text="n.message"></p>
                            <p class="mt-2 text-[10px] uppercase tracking-wide text-slate-500" x-text="n.time_ago"></p>
                        </a>
                    </template>
                </div>
                <div class="shrink-0 border-t border-slate-700 bg-slate-900/95 p-3">
                    <a href="{{ route('admin.notifications.index') }}" class="text-xs text-cyan-400 hover:underline">Historial de notificaciones</a>
                </div>
            </aside>
        </div>
    </template>
</div>
<script>
    function adminNotifDrawer() {
        return {
            open: false,
            unread: 0,
            items: [],
            pollMs: 45000,
            init() {
                this.refreshCount();
                setInterval(() => this.refreshCount(), this.pollMs);
            },
            async refreshCount() {
                try {
                    const r = await fetch('{{ route('admin.notifications.unread-count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    const j = await r.json();
                    this.unread = j.count || 0;
                } catch (e) {}
            },
            async load() {
                try {
                    const r = await fetch('{{ route('admin.notifications.recent') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    const j = await r.json();
                    this.items = (j.notifications || []).map(n => ({
                        ...n,
                        url: n.data && n.data.order_id ? '{{ url('/admin/orders') }}/' + n.data.order_id : '#',
                    }));
                } catch (e) { this.items = []; }
            },
            async markReadThenGo(n) {
                try {
                    await fetch(`{{ url('/admin/notifications') }}/${n.id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) {}
                this.open = false;
                window.location.href = n.url;
            },
        };
    }
</script>
@endif
