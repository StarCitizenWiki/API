@component('components.navs.nav_element', [
    'route' => route('admin_urls_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    __LOC__ShortUrls
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin_urls_whitelist_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        list
    @endcomponent
    __LOC__UrlWhitelist
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin_urls_whitelist_add_form'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        plus-circle
    @endcomponent
    __LOC__Add_Whitelist
@endcomponent