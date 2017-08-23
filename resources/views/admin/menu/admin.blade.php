@component('components.navs.nav_element', [
    'route' => route('admin_dashboard'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        dashboard
    @endcomponent
    @lang('Dashboard')
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_logs'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        book
    @endcomponent
    @lang('Logs')
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_routes_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        random
    @endcomponent
    @lang('Routes')
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_user_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        users
    @endcomponent
    @lang('Benutzer')
@endcomponent