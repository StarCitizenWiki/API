@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizenunpacked.items.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                cube
            @endcomponent
        </div>
        <div class="col">
            @lang('Items')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizen.manufacturers.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                industry
            @endcomponent
        </div>
        <div class="col">
            @lang('Hersteller')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizenunpacked.vehicles.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                rocket
            @endcomponent
        </div>
        <div class="col">
            @lang('Fahrzeuge')
        </div>
    </div>
@endcomponent
