@component('components.navs.nav_element', [
    'route' => route('web.admin.notifications.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                bullhorn
            @endcomponent
        </div>
        <div class="col">
            @lang('Benachrichtigungen')
        </div>
    </div>
@endcomponent

@can('web.admin.notifications.create')
    @component('components.navs.nav_element', [
        'route' => route('web.admin.notifications.create'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    plus
                @endcomponent
            </div>
            <div class="col">
                @lang('Benachrichtigung hinzufügen')
            </div>
        </div>
    @endcomponent
@endcan