@component('components.navs.nav_element', [
    'route' => route('web.starcitizen.vehicles.ships.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                rocket
            @endcomponent
        </div>
        <div class="col">
            @lang('Raumschiffe')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.starcitizen.vehicles.ground-vehicles.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                car
            @endcomponent
        </div>
        <div class="col">
            @lang('Bodenfahrzeuge')
        </div>
    </div>
@endcomponent

@can('web.translations.view')
    @component('components.navs.nav_element', [
        'route' => route('web.starcitizen.vehicles.sizes.index'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    expand-alt
                @endcomponent
            </div>
            <div class="col">
                @lang('Fahrzeuggrößen')
            </div>
        </div>
    @endcomponent

    @component('components.navs.nav_element', [
        'route' => route('web.starcitizen.vehicles.foci.index'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    crosshairs
                @endcomponent
            </div>
            <div class="col">
                @lang('Fahrzeugfokusse')
            </div>
        </div>
    @endcomponent

    @component('components.navs.nav_element', [
        'route' => route('web.starcitizen.vehicles.types.index'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    id-card
                @endcomponent
            </div>
            <div class="col">
                @lang('Fahrzeugtypen')
            </div>
        </div>
    @endcomponent
@endcan