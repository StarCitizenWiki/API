@component('components.navs.sidebar_section', ['class' => 'mb-3', 'titleClass' => 'text-muted', 'contentClass' => 'pl-2 mb-0', 'isLink' => 1, 'id' => 'm_admin', 'show' => 1])
    @slot('title')
        @lang('layouts/admin.admin')
    @endslot

    @include('admin.menu.admin')
@endcomponent

@component('components.navs.sidebar_section', ['class' => 'mb-3', 'titleClass' => 'text-muted', 'contentClass' => 'pl-2 mb-0', 'isLink' => 1, 'id' => 'm_app'])
    @slot('title')
        @lang('layouts/admin.app') Links
    @endslot

    @include('admin.menu.app_links')
@endcomponent

@component('components.navs.sidebar_section', ['class' => 'mb-3', 'titleClass' => 'text-muted', 'contentClass' => 'pl-2 mb-0', 'isLink' => 1, 'id' => 'm_urls'])
    @slot('title')
        @lang('layouts/admin.urls')
    @endslot

    @include('admin.menu.urls')
@endcomponent

@component('components.navs.sidebar_section', ['class' => 'mb-3', 'titleClass' => 'text-muted', 'contentClass' => 'pl-2 mb-0', 'isLink' => 1, 'id' => 'm_starmap'])
    @slot('title')
        @lang('layouts/admin.starmap')
    @endslot

    @include('admin.menu.starmap')
@endcomponent

@component('components.navs.sidebar_section', ['class' => 'mb-3', 'titleClass' => 'text-muted', 'contentClass' => 'pl-2 mb-0', 'isLink' => 1, 'id' => 'm_ships'])
    @slot('title')
        @lang('layouts/admin.ships')
    @endslot

    @include('admin.menu.ships')
@endcomponent