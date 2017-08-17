@component('components.navs.sidebar_section', [
    'titleClass' => 'text-muted',
    'contentClass' => 'pl-2',
    'title' => trans('api/index.documentation')
])

    @include('api.menu.documentation')
@endcomponent

@component('components.navs.sidebar_section', [
    'titleClass' => 'text-muted',
    'contentClass' => 'pl-2',
    'title' => trans('api/index.follow_us')
])

    @include('api.menu.social')
@endcomponent