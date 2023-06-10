@component('components.navs.nav_element')
    @slot('route')
        {{ config('app.url') }}
    @endslot
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                home
            @endcomponent
        </div>
        <div class="col">
            @lang('Startseite')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => 'https://status.star-citizen.wiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                signal
            @endcomponent
        </div>
        <div class="col">
            @lang('API Status')
        </div>
    </div>
@endcomponent