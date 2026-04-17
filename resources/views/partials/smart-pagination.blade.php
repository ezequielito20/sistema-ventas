@if (isset($items) && method_exists($items, 'hasPages') && $items->hasPages())
    @php
        $currentPage = $items->currentPage();
        $lastPage = $items->lastPage();

        $buildLinks = function (int $windowSize) use ($currentPage, $lastPage, $items) {
            if ($lastPage <= 1) {
                return [];
            }

            $links = [];
            $links[] = ['label' => 1, 'page' => 1, 'url' => $items->url(1), 'active' => $currentPage === 1, 'separator' => false];

            $start = max(2, $currentPage - $windowSize);
            $end = min($lastPage - 1, $currentPage + $windowSize);

            if ($start > 2) {
                $links[] = ['label' => '...', 'separator' => true];
            }

            for ($i = $start; $i <= $end; $i++) {
                $links[] = ['label' => $i, 'page' => $i, 'url' => $items->url($i), 'active' => $currentPage === $i, 'separator' => false];
            }

            if ($end < $lastPage - 1) {
                $links[] = ['label' => '...', 'separator' => true];
            }

            if ($lastPage > 1) {
                $links[] = ['label' => $lastPage, 'page' => $lastPage, 'url' => $items->url($lastPage), 'active' => $currentPage === $lastPage, 'separator' => false];
            }

            return $links;
        };

        $mobileLinks = $buildLinks(0);
        $desktopLinks = $buildLinks(1);
    @endphp

    <style>
        .custom-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            color: #718096;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 0;
        }

        .pagination-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            color: #4a5568;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            text-decoration: none !important;
        }

        .pagination-btn:hover:not([disabled]) {
            background-color: #f8fafc;
            border-color: #cbd5e0;
            color: #2d3748;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .pagination-btn[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f1f5f9;
        }

        .pagination-btn-icon {
            width: 2.4rem;
            height: 2.4rem;
            padding: 0;
            justify-content: center;
            flex-shrink: 0;
        }

        .page-numbers {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            min-width: 0;
        }

        .page-numbers-strip {
            min-width: 0;
            flex: 1 1 auto;
            overflow-x: auto;
        }

        .page-numbers-track {
            display: inline-flex;
            min-width: max-content;
            align-items: center;
            gap: 0.25rem;
        }

        .page-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4a5568;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .page-number:hover:not(.active) {
            background-color: #f1f5f9;
            color: #1a202c;
        }

        .page-number.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
        }

        .page-separator {
            color: #a0aec0;
            padding: 0 0.25rem;
            font-weight: bold;
        }

        @media (max-width: 640px) {
            .custom-pagination {
                flex-direction: column;
                align-items: stretch;
                text-align: left;
            }

            .pagination-controls {
                gap: 0.5rem;
            }

            .pagination-controls--desktop {
                display: none;
            }
        }

        @media (min-width: 641px) {
            .pagination-controls--mobile {
                display: none;
            }
        }
    </style>

    <div class="mt-8 px-6 pb-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
            <div class="custom-pagination">
                <div class="pagination-info">
                    <span>Mostrando
                        <span class="font-bold text-gray-900">{{ $items->firstItem() ?? 0 }}</span>
                        a
                        <span class="font-bold text-gray-900">{{ $items->lastItem() ?? 0 }}</span>
                        de
                        <span class="font-bold text-gray-900">{{ $items->total() }}</span>
                        {{ $label ?? 'registros' }}</span>
                </div>

                <div class="pagination-controls">
                    <div class="pagination-controls pagination-controls--mobile">
                        @if ($items->currentPage() > 1)
                            <a href="{{ $items->url($items->currentPage() - 1) }}" class="pagination-btn pagination-btn-icon" aria-label="Anterior">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                        @else
                            <button class="pagination-btn pagination-btn-icon" disabled aria-label="Anterior">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                        @endif

                        <div class="page-numbers-strip">
                            <div class="page-numbers-track">
                                @foreach ($mobileLinks as $link)
                                    @if (!empty($link['separator']))
                                        <span class="page-separator">{{ $link['label'] }}</span>
                                    @else
                                        @if ($link['active'])
                                            <span class="page-number active">{{ $link['label'] }}</span>
                                        @else
                                            <a href="{{ $link['url'] }}" class="page-number">{{ $link['label'] }}</a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        @if ($items->hasMorePages())
                            <a href="{{ $items->nextPageUrl() }}" class="pagination-btn pagination-btn-icon" aria-label="Siguiente">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        @else
                            <button class="pagination-btn pagination-btn-icon" disabled aria-label="Siguiente">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        @endif
                    </div>

                    <div class="pagination-controls pagination-controls--desktop">
                        @if ($items->currentPage() > 1)
                            <a href="{{ $items->url($items->currentPage() - 1) }}" class="pagination-btn">
                                <i class="fas fa-chevron-left text-xs"></i>
                                Anterior
                            </a>
                        @else
                            <button class="pagination-btn" disabled>
                                <i class="fas fa-chevron-left text-xs"></i>
                                Anterior
                            </button>
                        @endif

                        <div class="page-numbers-strip">
                            <div class="page-numbers-track">
                                @foreach ($desktopLinks as $link)
                                    @if (!empty($link['separator']))
                                        <span class="page-separator">{{ $link['label'] }}</span>
                                    @else
                                        @if ($link['active'])
                                            <span class="page-number active">{{ $link['label'] }}</span>
                                        @else
                                            <a href="{{ $link['url'] }}" class="page-number">{{ $link['label'] }}</a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        @if ($items->hasMorePages())
                            <a href="{{ $items->nextPageUrl() }}" class="pagination-btn">
                                Siguiente
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        @else
                            <button class="pagination-btn" disabled>
                                Siguiente
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
