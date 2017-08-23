@component('components.navs.nav_element', [
    'route' => route('api_status'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        dot-circle-o
    @endcomponent
    @lang('Api Status')
@endcomponent