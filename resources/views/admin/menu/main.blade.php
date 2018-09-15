@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
])
    @include('admin.menu.app_links')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_admin',
    'title' => __('Admin'),
])
    @include('admin.menu.admin')
@endcomponent

@can('web.admin.notifications.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_notifications',
        'title' => __('Benachrichtigungen'),
    ])
        @include('admin.menu.notifications')
    @endcomponent
@endcan

{{--
@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_starmap',
    'title' => __('Starmap'),
])
    @include('admin.menu.starmap')
@endcomponent
--}}

@can('web.admin.starcitizen.vehicles.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_vehicles',
        'title' => __('Fahrzeuge'),
    ])
        @include('admin.menu.vehicles')
    @endcomponent
@endcan

@can('web.admin.starcitizen.manufacturers.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_manufacturer',
        'title' => __('Hersteller'),
    ])
        @include('admin.menu.manufacturer')
    @endcomponent
@endcan

@can('web.admin.translations.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_translations',
        'title' => __('Übersetzungen'),
    ])
        @include('admin.menu.translations')
    @endcomponent
@endcan

@can('web.admin.rsi.comm-links.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mr-4 mr-md-0 mb-5',
        'titleClass' => 'text-muted pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_comm_links',
        'title' => __('Comm Link'),
    ])
        @include('admin.menu.comm_links')
    @endcomponent
@endcan