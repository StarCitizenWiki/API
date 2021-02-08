@component('components.navs.nav_element', [
    'route' => route('web.api.status'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                dot-circle
            @endcomponent
        </div>
        <div class="col">
            @lang('Api Status')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.dashboard'),
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