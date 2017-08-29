@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.api_url') }}
    @endslot
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                cogs
            @endcomponent
        </div>
        <div class="col">
            @lang('API')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.tools_url') }}
    @endslot
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                wrench
            @endcomponent
        </div>
        <div class="col">
            @lang('Tools')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element')
    @slot('route')
        //{{ config('app.shorturl_url') }}
    @endslot
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                link
            @endcomponent
        </div>
        <div class="col">
            @lang('ShortUrls')
        </div>
    </div>
@endcomponent