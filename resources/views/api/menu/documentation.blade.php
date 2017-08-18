@component('components.navs.nav_element', [
    'route' => route('api_faq'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        question-circle
    @endcomponent
    @lang('api/index.faq')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        cloud
    @endcomponent
    @lang('api/index.rsi_api')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        rocket
    @endcomponent
    @lang('api/index.wiki_api')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    @lang('api/index.url_api')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        image
    @endcomponent
    @lang('api/index.media_api')
@endcomponent