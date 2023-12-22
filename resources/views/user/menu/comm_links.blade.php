@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                satellite
            @endcomponent
        </div>
        <div class="col">
            @lang('Comm-Links')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.search'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                search
            @endcomponent
        </div>
        <div class="col">
            @lang('Suche')
        </div>
    </div>
@endcomponent

@can('web.user.rsi.comm-links.view')
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
                @lang('Channels')
            </div>
        </div>
    @endcomponent

    @component('components.navs.nav_element', [
        'route' => route('web.user.rsi.comm-links.series.index'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    bookmark
                @endcomponent
            </div>
            <div class="col">
                @lang('Serien')
            </div>
        </div>
    @endcomponent

    @component('components.navs.nav_element', [
        'route' => route('web.user.rsi.comm-links.image-tags.index'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    images
                @endcomponent
            </div>
            <div class="col">
                @lang('Bilder Tags')
            </div>
        </div>
    @endcomponent
@endcan

@component('components.navs.nav_element', [
    'route' => route('web.user.rsi.comm-links.images.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                images
            @endcomponent
        </div>
        <div class="col">
            @lang('Bilder')
        </div>
    </div>
@endcomponent