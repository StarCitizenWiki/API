@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
])
    @include('api.menu.status')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mb-md-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => __('Dokumentation'),
])
    @include('api.menu.documentation')
@endcomponent

@component('components.navs.sidebar_section', [
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => __('Follow Us'),
])
    @include('api.menu.social')
@endcomponent