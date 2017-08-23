@component('components.navs.nav_element', [
    'route' => route('account_urls_list'),
])
    {{-- Slot --}}
    @component('components.elements.icon', ['class' => 'mr-2'])
        bars
    @endcomponent
    @lang('ShortUrls')
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('account_urls_add_form'),
])
    {{-- Slot --}}
    @component('components.elements.icon', ['class' => 'mr-2'])
        plus
    @endcomponent
    @lang('Erstellen')
@endcomponent