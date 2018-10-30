@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizen.starmap.systems.index'),
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
    'route' => route('web.user.starcitizen.starmap.celestialobjects.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                circle-notch
            @endcomponent
        </div>
        <div class="col">
            @lang('Celestial Objects')
        </div>
    </div>
@endcomponent
