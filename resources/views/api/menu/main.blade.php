@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
])
    @include('api.menu.status')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => trans('api/index.documentation')
])
    @include('api.menu.documentation')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'title' => trans('api/index.follow_us')
])
    @include('api.menu.social')
@endcomponent