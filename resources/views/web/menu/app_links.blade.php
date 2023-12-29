@component('components.navs.nav_element', [
    'route' => route('web.dashboard'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                tachometer-alt
            @endcomponent
        </div>
        <div class="col">
            @lang('Dashboard')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => 'https://status.star-citizen.wiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                signal
            @endcomponent
        </div>
        <div class="col">
            @lang('API Status')
        </div>
    </div>
@endcomponent