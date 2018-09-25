@component('components.navs.nav_element', [
    'route' => route('web.user.notifications.index'),
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

@can('web.user.notifications.create')
    @component('components.navs.nav_element', [
        'route' => route('web.user.notifications.create'),
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