@component('components.navs.nav_element', [
    'route' => route('admin.notification.list'),
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
    'route' => route('admin.notification.add_form'),
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