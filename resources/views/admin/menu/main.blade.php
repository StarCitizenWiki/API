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
    'title' => trans('layouts/admin.admin'),
])
    @include('admin.menu.admin')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_urls',
    'title' => trans('layouts/admin.urls'),
])
    @include('admin.menu.urls')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_starmap',
    'title' => trans('layouts/admin.starmap'),
])
    @include('admin.menu.starmap')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_ships',
    'title' => trans('layouts/admin.ships'),
])
    @include('admin.menu.ships')
@endcomponent