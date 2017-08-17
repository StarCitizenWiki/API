@component('components.navs.nav_element', [
    'contentClass' => 'text-white',
    'route' => 'https://star-citizen.wiki/'
])

    @component('components.elements.icon', ['class' => 'mr-2'])
        globe
    @endcomponent
    star-citizen.wiki
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'text-white',
    'route' => 'https://twitter.com/SC_Wiki'
])

    @component('components.elements.icon', ['class' => 'mr-2'])
        twitter
    @endcomponent
    SC_Wiki
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'text-white',
    'route' => 'https://facebook.com/StarCitizenWiki'
])

    @component('components.elements.icon', ['class' => 'mr-2'])
        facebook-square
    @endcomponent
    StarCitizenWiki
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'text-white',
    'route' => 'https://robertsspaceindustries.com/orgs/WIKI'
])

    @component('components.elements.icon', ['class' => 'mr-2'])
        building-o
    @endcomponent
    WIKI
@endcomponent