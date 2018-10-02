@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                comment-alt
            @endcomponent
        </div>
        <div class="col">
            @lang('Comm Links')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.categories.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                tag
            @endcomponent
        </div>
        <div class="col">
            @lang('Kategorien')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.channels.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                circle
            @endcomponent
        </div>
        <div class="col">
            @lang('Channel')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.series.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                books
            @endcomponent
        </div>
        <div class="col">
            @lang('Serien')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.images.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                image
            @endcomponent
        </div>
        <div class="col">
            @lang('Bilder')
        </div>
    </div>
@endcomponent
