@component('components.navs.sidebar_section', [
    'titleClass' => 'text-muted',
    'contentClass' => 'pl-2',
    'isLink' => 1,
    'id' => 'm_account',
    'title' => 'Account',
    'show' => 1,
])
    @include('api.auth.menu.account')
@endcomponent

@component('components.navs.sidebar_section', [
    'titleClass' => 'text-muted',
    'contentClass' => 'pl-2',
    'isLink' => 1,
    'id' => 'm_shorturls',
    'title' => 'ShortURLs',
])
    @include('api.auth.menu.shorturls')
@endcomponent