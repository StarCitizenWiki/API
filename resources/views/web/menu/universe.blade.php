@component('components.navs.nav_element', [
    'route' => route('web.starcitizenunpacked.items.index'),
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

@component('components.navs.nav_element', [
    'route' => route('web.starcitizenunpacked.vehicles.index'),
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

@component('components.navs.nav_element', [
    'route' => route('web.starcitizen.manufacturers.index'),
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