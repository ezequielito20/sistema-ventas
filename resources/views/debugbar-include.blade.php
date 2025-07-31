@if(config('app.debug') && config('debugbar.enabled'))
    @php
        if (app()->bound('debugbar')) {
            $debugbar = app('debugbar');
            $debugbar->enable();
            $renderer = $debugbar->getJavascriptRenderer();
            echo $renderer->renderHead();
            echo $renderer->render();
        }
    @endphp
@endif 