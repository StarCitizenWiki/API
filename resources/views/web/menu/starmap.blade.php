@component('components.navs.nav_element', [
    'route' => route('web.starcitizen.starmap.starsystems.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                sun
            @endcomponent
        </div>
        <div class="col">
            @lang('Systeme')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.starcitizen.starmap.celestial_objects.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                meteor
            @endcomponent
        </div>
        <div class="col">
            @lang('Objekte')
        </div>
    </div>
@endcomponent
