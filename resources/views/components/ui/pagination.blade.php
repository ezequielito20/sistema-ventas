@props([
    'paginator',
    /** Selector CSS para scrollIntoView tras cambiar de página (opcional). Ej: ".ui-panel" */
    'scrollIntoView' => null,
])

@if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginator->hasPages())
@php
    $window = \Illuminate\Pagination\UrlWindow::make($paginator);
    $elements = array_filter([
        $window['first'],
        is_array($window['slider']) ? '...' : null,
        $window['slider'],
        is_array($window['last']) ? '...' : null,
        $window['last'],
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

    {{-- Móvil: anterior / siguiente --}}
    <div class="flex justify-between gap-2 sm:hidden">
        @if ($paginator->onFirstPage())
            <span class="ui-page-link opacity-40" aria-disabled="true">Anterior</span>
        @else
            <button
                type="button"
                wire:click="previousPage('{{ $pageName }}')"
                @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                wire:loading.attr="disabled"
                class="ui-page-link"
            >
                Anterior
            </button>
        @endif
        @if ($paginator->hasMorePages())
            <button
                type="button"
                wire:click="nextPage('{{ $pageName }}')"
                @if ($scrollJs) x-on:click="{{ $scrollJs }}" @endif
                wire:loading.attr="disabled"
                class="ui-page-link"
            >
                Siguiente
            </button>
        @else
            <span class="ui-page-link opacity-40" aria-disabled="true">Siguiente</span>
        @endif
    </div>

    {{-- Desktop: segmentado --}}
    <div class="hidden sm:block">
        <div class="ui-pagination ui-pagination--segmented inline-flex max-w-full flex-nowrap overflow-x-auto">
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

            @foreach ($elements as $element)
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
