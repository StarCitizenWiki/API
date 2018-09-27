@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
])
    @include('user.menu.app_links')
@endcomponent

@can('web.user.dashboard.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-md-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_admin',
        'title' => __('Admin'),
    ])
        @include('user.menu.admin')
    @endcomponent
@endcan

@can('web.user.notifications.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-md-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_notifications',
        'title' => __('Benachrichtigungen'),
    ])
        @include('user.menu.notifications')
    @endcomponent
@endcan

{{--
@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_starmap',
    'title' => __('Starmap'),
])
    @include('user.menu.starmap')
@endcomponent
--}}

@can('web.user.starcitizen.vehicles.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-md-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_vehicles',
        'title' => __('Fahrzeuge'),
    ])
        @include('user.menu.vehicles')
    @endcomponent
@endcan

@can('web.user.starcitizen.manufacturers.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-md-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_manufacturer',
        'title' => __('Hersteller'),
    ])
        @include('user.menu.manufacturer')
    @endcomponent
@endcan

@can('web.user.translations.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-md-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_translations',
        'title' => __('Übersetzungen'),
    ])
        @include('user.menu.translations')
    @endcomponent
@endcan

@can('web.user.rsi.comm-links.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-5',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_comm_links',
        'title' => __('Comm Link'),
    ])
        @include('user.menu.comm_links')
    @endcomponent
@endcan