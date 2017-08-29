@component('components.navs.nav_element', [
    'route' => 'https://star-citizen.wiki/',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                globe
            @endcomponent
        </div>
        <div class="col">
            star-citizen.wiki
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://twitter.com/SC_Wiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                twitter
            @endcomponent
        </div>
        <div class="col">
            SC_Wiki
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://facebook.com/StarCitizenWiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                facebook-square
            @endcomponent
        </div>
        <div class="col">
            StarCitizenWiki
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://robertsspaceindustries.com/orgs/WIKI',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                building-o
            @endcomponent
        </div>
        <div class="col">
            WIKI
        </div>
    </div>
@endcomponent