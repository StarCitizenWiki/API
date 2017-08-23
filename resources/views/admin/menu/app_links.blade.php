@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.api_url') }}
    @endslot
    @component('components.elements.icon', ['class' => 'mr-2'])
        cogs
    @endcomponent
    @lang('API')
@endcomponent


@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.tools_url') }}
    @endslot
    @component('components.elements.icon', ['class' => 'mr-2'])
        wrench
    @endcomponent
    @lang('Tools')
@endcomponent


@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.shorturl_url') }}
    @endslot
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    @lang('ShortUrls')
@endcomponent