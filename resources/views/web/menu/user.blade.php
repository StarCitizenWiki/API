@can('web.users.view')
    @component('components.navs.nav_element', [
        'route' => route('web.users.index'),
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

@can('web.changelogs.view')
    @component('components.navs.nav_element', [
        'route' => route('web.changelogs.index'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    pen
                @endcomponent
            </div>
            <div class="col">
                @lang('Änderungen')
            </div>
        </div>
    @endcomponent
@endcan


@can('web.jobs.upload_csv')
    @component('components.navs.nav_element', [
        'route' => route('web.jobs.failed'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    ghost
                @endcomponent
            </div>
            <div class="col">
                @lang('Fehlgeschlagene Jobs')
            </div>
        </div>
    @endcomponent
@endcan