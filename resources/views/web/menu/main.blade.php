@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
])
    @include('web.menu.app_links')
@endcomponent

@auth
@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_admin',
    'title' => __('Benutzer'),
])
    @include('web.menu.user')
@endcomponent
@endauth

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_comm_links',
    'title' => __('Comm-Link'),
])
    @include('web.menu.comm_links')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_manufacturer',
    'title' => __('Universum'),
])
    @include('web.menu.universe')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_galactapedia',
    'title' => __('Galactapedia'),
])
    @include('web.menu.galactapedia')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_starmap',
    'title' => __('Starmap'),
])
    @include('web.menu.starmap')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_stats',
    'title' => __('Statistiken'),
])
    @include('web.menu.stats')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_vehicles',
    'title' => __('Ship-Matrix'),
])
    @include('web.menu.vehicles')
@endcomponent

@can('web.translations.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-md-2',
        'titleClass' => 'pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_translations',
        'title' => __('Übersetzungen'),
    ])
        @include('web.menu.translations')
    @endcomponent
@endcan

@can('web.transcripts.view')
    @component('components.navs.sidebar_section', [
        'class' => 'mb-md-2',
        'titleClass' => 'pb-0',
        'contentClass' => 'pl-3 pl-md-2',
        'id' => 'm_transcripts',
        'title' => __('Transkript'),
    ])
        @include('web.menu.transcripts')
    @endcomponent
@endcan

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => __('Dokumentation'),
])
    @include('web.menu.documentation')
@endcomponent
