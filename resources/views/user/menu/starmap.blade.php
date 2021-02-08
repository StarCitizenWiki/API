@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizen.starmap.starsystems.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                circle-notch
            @endcomponent
        </div>
        <div class="col">
            @lang('Systeme')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizen.starmap.celestial_objects.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                dot-circle
            @endcomponent
        </div>
        <div class="col">
            @lang('Objekte')
        </div>
    </div>
@endcomponent
