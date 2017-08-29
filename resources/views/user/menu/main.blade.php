@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_account',
    'title' => __('Account'),
    'show' => 1,
])
    @include('user.menu.account')
@endcomponent

@component('components.navs.sidebar_section', [
    'class' => 'mr-4 mr-md-0 mb-md-5 mb-lg-2',
    'titleClass' => 'text-muted pb-0',
    'contentClass' => 'pl-3 pl-md-2',
    'id' => 'm_shorturls',
    'title' => __('ShortUrls'),
])
    @include('user.menu.urls')
@endcomponent