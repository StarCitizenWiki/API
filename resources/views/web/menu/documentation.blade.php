@component('components.navs.nav_element', [
    'route' => 'https://github.com/StarCitizenWiki/API'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon', ['type' => 'fab'])
                github
            @endcomponent
        </div>
        <div class="col">
            @lang('Quellcode')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://docs.star-citizen.wiki/v2.html'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                cloud
            @endcomponent
        </div>
        <div class="col">
            @lang('RSI API')
        </div>
    </div>
@endcomponent
