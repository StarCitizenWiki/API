@component('components.navs.nav_element')
    @slot('route')
        {{ config('app.url') }}
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