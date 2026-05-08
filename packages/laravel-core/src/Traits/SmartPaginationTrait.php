<?php

namespace MiEmpresa\Core\Traits;


/**
 * Trait para generar paginación inteligente con ventana dinámica
 * 
 * @property array $smartLinks
 * @property bool $hasPrevious
 * @property bool $hasNext
 * @property string|null $previousPageUrl
 * @property string|null $nextPageUrl
 * @property string $firstPageUrl
 * @property string $lastPageUrl
 */
trait SmartPaginationTrait
{
    /**
     * Genera paginación inteligente con ventana dinámica
     * 
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param int $windowSize
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function generateSmartPagination($paginator, $windowSize = 2)
    {
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        if ($lastPage <= 1) {
            // No hay paginación
            $paginator->smartLinks = [];
            $paginator->hasPrevious = false;
            $paginator->hasNext = false;
            $paginator->previousPageUrl = null;
            $paginator->nextPageUrl = null;
            $paginator->firstPageUrl = null;
            $paginator->lastPageUrl = null;
            return $paginator;
        }

        $smartLinks = [];

        // Siempre mostrar primera página
        $smartLinks[] = [
            'page' => 1,
            'url' => $paginator->url(1),
            'label' => 1,
            'active' => $currentPage == 1,
            'isSeparator' => false,
        ];

        // Calcular rango de ventana centrado en la página actual
        $start = max(2, $currentPage - $windowSize);
        $end = min($lastPage - 1, $currentPage + $windowSize);

        // Separador izquierdo si hay hueco entre 1 y el inicio de la ventana
        if ($start > 2) {
            $smartLinks[] = [
                'page' => '...',
                'url' => null,
                'label' => '...',
                'active' => false,
                'isSeparator' => true,
            ];
        }

        // Páginas de la ventana
        for ($i = $start; $i <= $end; $i++) {
            $smartLinks[] = [
                'page' => $i,
                'url' => $paginator->url($i),
                'label' => $i,
                'active' => $i == $currentPage,
                'isSeparator' => false,
            ];
        }

        // Separador derecho si hay hueco entre el final de la ventana y la última página
        if ($end < $lastPage - 1) {
            $smartLinks[] = [
                'page' => '...',
                'url' => null,
                'label' => '...',
                'active' => false,
                'isSeparator' => true,
            ];
        }

        // Siempre mostrar última página (si hay más de una)
        if ($lastPage > 1) {
            $smartLinks[] = [
                'page' => $lastPage,
                'url' => $paginator->url($lastPage),
                'label' => $lastPage,
                'active' => $currentPage == $lastPage,
                'isSeparator' => false,
            ];
        }

        // Info adicional de navegación
        $paginator->smartLinks = $smartLinks;
        $paginator->hasPrevious = $paginator->previousPageUrl() !== null;
        $paginator->hasNext = $paginator->nextPageUrl() !== null;
        $paginator->previousPageUrl = $paginator->previousPageUrl();
        $paginator->nextPageUrl = $paginator->nextPageUrl();
        $paginator->firstPageUrl = $paginator->url(1);
        $paginator->lastPageUrl = $paginator->url($lastPage);

        return $paginator;
    }
}
