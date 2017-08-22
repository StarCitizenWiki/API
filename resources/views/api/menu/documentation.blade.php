@component('components.navs.nav_element', [
    'route' => route('api_faq'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        question-circle
    @endcomponent
    __LOC__Faq
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        cloud
    @endcomponent
    __LOC__Rsi Api
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        rocket
    @endcomponent
    __LOC__Wiki Api
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    __LOC__Url Api
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        image
    @endcomponent
    __LOC__Media Api
@endcomponent