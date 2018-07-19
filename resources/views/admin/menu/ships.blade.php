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
