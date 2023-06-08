@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
])
    @include('api.menu.status')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => __('Benutzer'),
])
    @include('api.menu.user')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => __('Dokumentation'),
])
    @include('api.menu.documentation')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => __('Social Media'),
])
    @include('api.menu.social')
@endcomponent