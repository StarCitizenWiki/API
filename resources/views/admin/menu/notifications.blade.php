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
            @lang('Notifications')
        </div>
    </div>
@endcomponent

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
            @lang('Notification hinzufügen')
        </div>
    </div>
@endcomponent