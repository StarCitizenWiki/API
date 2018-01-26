@component('components.navs.nav_element', [
    'route' => route('admin.dashboard'),
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


@component('components.navs.nav_element', [
    'route' => route('admin.logs'),
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
    'route' => route('admin.user.list'),
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