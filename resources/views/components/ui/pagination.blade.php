@props([
    'paginator',
    /** Selector CSS para scrollIntoView tras cambiar de página (opcional). Ej: ".ui-panel" */
    'scrollIntoView' => null,
])

@if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginator->hasPages())
@php
    $mobilePaginator = clone $paginator;
    $mobileWindow = \Illuminate\Pagination\UrlWindow::make($mobilePaginator->onEachSide(0));
    $mobileElements = array_filter([
        $mobileWindow['first'],
        is_array($mobileWindow['slider']) ? '...' : null,
        $mobileWindow['slider'],
        is_array($mobileWindow['last']) ? '...' : null,
        $mobileWindow['last'],
    ]);

    $desktopPaginator = clone $paginator;
    $desktopWindow = \Illuminate\Pagination\UrlWindow::make($desktopPaginator->onEachSide(1));
    $desktopElements = array_filter([
        $desktopWindow['first'],
        is_array($desktopWindow['slider']) ? '...' : null,
        $desktopWindow['slider'],
        is_array($desktopWindow['last']) ? '...' : null,
        $desktopWindow['last'],
    ]);

    $pageName = $paginator->getPageName();

    $scrollJs = '';
    if ($scrollIntoView) {
        $sel = addcslashes((string) $scrollIntoView, "\\'");
        $scrollJs = "(function(el){var t=el.closest('{$sel}')||document.querySelector('{$sel}');if(t)t.scrollIntoView({block:'nearest'});})(\$el)";
    }
@endphp
<nav
    role="navigation"
    aria-label="Paginación"
    class="ui-pagination-bar flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    wire:key="pagination-{{ $paginator->getPageName() }}-{{ $paginator->currentPage() }}"
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
        <p class="text-sm leading-5 text-slate-400">
            @if ($paginator->firstItem())
                Mostrando
                <span class="font-medium text-slate-200">{{ $paginator->firstItem() }}</span>
                a
                <span class="font-medium text-slate-200">{{ $paginator->lastItem() }}</span>
                de
                <span class="font-medium text-slate-200">{{ $paginator->total() }}</span>
                resultados
            @else
                <span class="font-medium text-slate-200">{{ $paginator->count() }}</span>
                resultado(s)
            @endif
        </p>

        <div class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-400">
            <span>Registros por página:</span>
            <select
                class="rounded-lg border border-slate-600 bg-slate-950/60 py-1.5 pl-2 pr-7 text-xs sm:text-[0.8rem] text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                wire:model.live="perPage"
            >
                @php
                    $options = [10, 25, 50, 100];
                    $currentPerPage = $paginator->perPage();
                    if (! in_array($currentPerPage, $options, true)) {
                        $options[] = $currentPerPage;
                    }
                    sort($options);
                @endphp
                @foreach ($options as $option)
                    <option value="{{ $option }}" @selected($option === $currentPerPage)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Móvil: anterior / páginas / siguiente (sin overflow del contenedor principal) --}}
    <div class="flex items-center gap-2 sm:hidden">
        @if ($paginator->onFirstPage())
            <span class="ui-page-link ui-page-link--edge opacity-40" aria-disabled="true" title="Anterior">
                <i class="fas fa-chevron-left text-[0.7rem]" aria-hidden="true"></i>
            </span>
        @else
            <button
                type="button"
                wire:click="previousPage('{{ $pageName }}')"
                @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                wire:loading.attr="disabled"
                class="ui-page-link ui-page-link--edge"
                title="Anterior"
            >
                <i class="fas fa-chevron-left text-[0.7rem]" aria-hidden="true"></i>
            </button>
        @endif

        <div class="min-w-0 flex-1">
            <div class="ui-pagination ui-pagination--segmented inline-flex max-w-full flex-nowrap">
                @foreach ($mobileElements as $element)
                    @if (is_string($element))
                        <span class="ui-page-link ui-page-link--ellipsis" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="mobile-paginator-{{ $pageName }}-page-{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span class="ui-page-link is-active" aria-current="page">{{ $page }}</span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="gotoPage({{ (int) $page }}, '{{ $pageName }}')"
                                        @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                                        wire:loading.attr="disabled"
                                        class="ui-page-link"
                                        aria-label="Ir a la página {{ $page }}"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>

        @if ($paginator->hasMorePages())
            <button
                type="button"
                wire:click="nextPage('{{ $pageName }}')"
                @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                wire:loading.attr="disabled"
                class="ui-page-link ui-page-link--edge"
                title="Siguiente"
            >
                <i class="fas fa-chevron-right text-[0.7rem]" aria-hidden="true"></i>
            </button>
        @else
            <span class="ui-page-link ui-page-link--edge opacity-40" aria-disabled="true" title="Siguiente">
                <i class="fas fa-chevron-right text-[0.7rem]" aria-hidden="true"></i>
            </span>
        @endif
    </div>

    {{-- Desktop: segmentado --}}
    <div class="hidden sm:block">
        <div class="ui-pagination ui-pagination--segmented inline-flex max-w-full flex-nowrap">
            @if ($paginator->onFirstPage())
                <span class="ui-page-link ui-page-link--edge opacity-40" aria-disabled="true" title="Anterior">
                    <i class="fas fa-chevron-left text-[0.7rem]" aria-hidden="true"></i>
                </span>
            @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $pageName }}')"
                    @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                    wire:loading.attr="disabled"
                    class="ui-page-link ui-page-link--edge"
                    title="Anterior"
                >
                    <i class="fas fa-chevron-left text-[0.7rem]" aria-hidden="true"></i>
                </button>
            @endif

            @foreach ($desktopElements as $element)
                @if (is_string($element))
                    <span class="ui-page-link ui-page-link--ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <span wire:key="paginator-{{ $pageName }}-page-{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <span class="ui-page-link is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <button
                                    type="button"
                                    wire:click="gotoPage({{ (int) $page }}, '{{ $pageName }}')"
                                    @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                                    wire:loading.attr="disabled"
                                    class="ui-page-link"
                                    aria-label="Ir a la página {{ $page }}"
                                >
                                    {{ $page }}
                                </button>
                            @endif
                        </span>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $pageName }}')"
                    @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                    wire:loading.attr="disabled"
                    class="ui-page-link ui-page-link--edge"
                    title="Siguiente"
                >
                    <i class="fas fa-chevron-right text-[0.7rem]" aria-hidden="true"></i>
                </button>
            @else
                <span class="ui-page-link ui-page-link--edge opacity-40" aria-disabled="true" title="Siguiente">
                    <i class="fas fa-chevron-right text-[0.7rem]" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    </div>
</nav>
@endif
