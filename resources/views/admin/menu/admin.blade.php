@component('components.navs.nav_element', ['contentClass' => 'text-white py-1', 'route' => 'admin_dashboard'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        dashboard
    @endcomponent
    @lang('layouts/admin.dashboard')
@endcomponent

@component('components.navs.nav_element', ['contentClass' => 'text-white py-1', 'route' => 'admin_logs'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        book
    @endcomponent
    @lang('layouts/admin.logs')
@endcomponent

@component('components.navs.nav_element', ['contentClass' => 'text-white py-1', 'route' => 'admin_routes_list'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        random
    @endcomponent
    @lang('layouts/admin.routes')
@endcomponent

@component('components.navs.nav_element', ['contentClass' => 'text-white py-1', 'route' => 'admin_users_list'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        users
    @endcomponent
    @lang('layouts/admin.user')
@endcomponent