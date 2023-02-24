@component('components.navs.nav_element', [
    'route' => route('web.user.dashboard'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                tachometer-alt
            @endcomponent
        </div>
        <div class="col">
            @lang('Dashboard')
        </div>
    </div>
@endcomponent

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

@can('web.user.changelogs.view')
    @component('components.navs.nav_element', [
        'route' => route('web.user.changelogs.index'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    pencil-alt
                @endcomponent
            </div>
            <div class="col">
                @lang('Änderungen')
            </div>
        </div>
    @endcomponent
@endcan


@can('web.user.jobs.upload_csv')
    @component('components.navs.nav_element', [
        'route' => route('web.user.jobs.failed'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    exclamation
                @endcomponent
            </div>
            <div class="col">
                @lang('Fehlgeschlagene Jobs')
            </div>
        </div>
    @endcomponent
@endcan