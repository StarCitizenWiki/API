@component('components.navs.nav_element', [
    'route' => route('admin_dashboard'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                dashboard
            @endcomponent
        </div>
        <div class="col">
            @lang('Dashboard')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_logs'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                book
            @endcomponent
        </div>
        <div class="col">
            @lang('Logs')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_routes_list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                random
            @endcomponent
        </div>
        <div class="col">
            @lang('Routes')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => route('admin_user_list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                users
            @endcomponent
        </div>
        <div class="col">
            @lang('Benutzer')
        </div>
    </div>
@endcomponent