@component('components.navs.nav_element', [
    'route' => route('api_faq'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        question-circle
    @endcomponent
    @lang('FAQ')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        cloud
    @endcomponent
    @lang('RSI Api')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        rocket
    @endcomponent
    @lang('Wiki Api')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    @lang('ShortUrl Api')
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        image
    @endcomponent
    @lang('Medien')
@endcomponent