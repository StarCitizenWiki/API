@component('components.navs.nav_element', [
    'route' => route('web.admin.rsi.comm_links.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                bullhorn
            @endcomponent
        </div>
        <div class="col">
            @lang('Comm Links')
        </div>
    </div>
@endcomponent
