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

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_notifications',
    'title' => __('Notifications'),
])
    @include('admin.menu.notifications')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_starmap',
    'title' => __('Starmap'),
])
    @include('admin.menu.starmap')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_ships',
    'title' => __('Ships'),
])
    @include('admin.menu.ships')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_manufacturer',
    'title' => __('Manufacturer'),
])
    @include('admin.menu.manufacturer')
@endcomponent