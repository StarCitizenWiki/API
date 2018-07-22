@component('components.navs.nav_element', [
    'route' => route('web.admin.starcitizen.vehicles.ships.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                rocket
            @endcomponent
        </div>
        <div class="col">
            @lang('Ships')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.admin.starcitizen.vehicles.sizes.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                expand-alt
            @endcomponent
        </div>
        <div class="col">
            @lang('Vehicle Sizes')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.admin.starcitizen.vehicles.foci.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                crosshairs
            @endcomponent
        </div>
        <div class="col">
            @lang('Vehicle Foci')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.admin.starcitizen.vehicles.types.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                id-card
            @endcomponent
        </div>
        <div class="col">
            @lang('Vehicle Types')
        </div>
    </div>
@endcomponent
