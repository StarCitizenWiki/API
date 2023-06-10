@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.stat.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                chart-column
            @endcomponent
        </div>
        <div class="col">
            @lang('Spendenstatistiken')
        </div>
    </div>
@endcomponent
