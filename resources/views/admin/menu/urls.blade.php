@component('components.navs.nav_element', [
    'route' => route('admin_urls_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    @lang('ShortUrls')
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin_urls_whitelist_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        list
    @endcomponent
    @lang('Erlaubte Domains')
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin_urls_whitelist_add_form'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        plus-circle
    @endcomponent
    @lang('Domain hinzufügen')
@endcomponent