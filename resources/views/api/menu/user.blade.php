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

@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizenunpacked.items.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                cookie-bite
            @endcomponent
        </div>
        <div class="col">
            @lang('Items')
        </div>
    </div>
@endcomponent