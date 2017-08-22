@component('components.navs.nav_element', [
    'route' => route('admin_dashboard'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        dashboard
    @endcomponent
    __LOC__Dashboard
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_logs'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        book
    @endcomponent
    __LOC__Logs
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_routes_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        random
    @endcomponent
    __LOC__Routes
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_user_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        users
    @endcomponent
    __LOC__Users
@endcomponent