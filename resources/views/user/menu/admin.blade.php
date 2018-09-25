@can('web.user.dashboard.view')
    @component('components.navs.nav_element', [
        'route' => route('web.user.dashboard'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    tachometer
                @endcomponent
            </div>
            <div class="col">
                @lang('Dashboard')
            </div>
        </div>
    @endcomponent
@endcan

@can('web.user.users.view')
    @component('components.navs.nav_element', [
        'route' => route('web.user.users.index'),
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
@endcan