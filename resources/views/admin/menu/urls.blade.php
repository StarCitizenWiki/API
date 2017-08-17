@component('components.navs.nav_element', ['contentClass' => 'text-white py-1', 'route' => 'admin_urls_list'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        link
    @endcomponent
    @lang('layouts/admin.short_urls')
@endcomponent

@component('components.navs.nav_element', ['contentClass' => 'text-white py-1', 'route' => 'admin_urls_whitelist_list'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        list
    @endcomponent
    @lang('layouts/admin.whitelist')
@endcomponent

@component('components.navs.nav_element', ['contentClass' => 'text-white py-1', 'route' => 'admin_urls_whitelist_add_form'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        plus-circle
    @endcomponent
    @lang('layouts/admin.add_whitelist')
@endcomponent