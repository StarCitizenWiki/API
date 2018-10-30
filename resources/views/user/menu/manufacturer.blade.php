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
