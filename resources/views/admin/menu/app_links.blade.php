@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.api_url') }}
    @endslot
    @component('components.elements.icon', ['class' => 'mr-2'])
        cogs
    @endcomponent
    __LOC__API
@endcomponent


@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.tools_url') }}
    @endslot
    @component('components.elements.icon', ['class' => 'mr-2'])
        wrench
    @endcomponent
    __LOC__Tools
@endcomponent


@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.shorturl_url') }}
    @endslot
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    __LOC__ShortUrl
@endcomponent