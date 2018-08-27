@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                question-circle
            @endcomponent
        </div>
        <div class="col">
            @lang('FAQ')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('web.api.documentation')
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                cloud
            @endcomponent
        </div>
        <div class="col">
            @lang('RSI Api')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                rocket
            @endcomponent
        </div>
        <div class="col">
            @lang('Wiki Api')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                image
            @endcomponent
        </div>
        <div class="col">
            @lang('Medien')
        </div>
    </div>
@endcomponent