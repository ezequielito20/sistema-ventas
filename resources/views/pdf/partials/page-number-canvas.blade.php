{{-- Requiere enable_php en la generación del PDF (solo vistas de confianza). Numeración en pie. --}}
<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $size = 8;
        $color = [0.33, 0.37, 0.45];
        $pdf->page_text(420, 758, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, $size, $color);
    }
</script>
