@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2', 'isLink' => 1, 'id' => 'm_admin'])
    @slot('title')
        @lang('layouts/admin.admin')
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_dashboard'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            dashboard
        @endcomponent
        @lang('layouts/admin.dashboard')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_logs'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            book
        @endcomponent
        @lang('layouts/admin.logs')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_routes_list'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            random
        @endcomponent
        @lang('layouts/admin.routes')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_users_list'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            users
        @endcomponent
        @lang('layouts/admin.user')
    @endcomponent
@endcomponent

@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2', 'isLink' => 1, 'id' => 'm_app'])
    @slot('title')
        @lang('layouts/admin.app') Links
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white'])
        @slot('route')
            //{{ config('app.api_url') }}
        @endslot
        @component('components.elements.icon', ['class' => 'mr-2'])
            cogs
        @endcomponent
        @lang('layouts/admin.api')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white'])
        @slot('route')
            //{{ config('app.tools_url') }}
        @endslot
        @component('components.elements.icon', ['class' => 'mr-2'])
            wrench
        @endcomponent
        @lang('layouts/admin.tools')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white'])
        @slot('route')
            //{{ config('app.shorturl_url') }}
        @endslot
        @component('components.elements.icon', ['class' => 'mr-2'])
            link
        @endcomponent
        @lang('layouts/admin.short_url')
    @endcomponent
@endcomponent

@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2', 'isLink' => 1, 'id' => 'm_urls'])
    @slot('title')
        @lang('layouts/admin.urls')
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_urls_list'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            link
        @endcomponent
        @lang('layouts/admin.short_urls')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_urls_whitelist_list'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            list
        @endcomponent
        @lang('layouts/admin.whitelist')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_urls_whitelist_add_form'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            plus-circle
        @endcomponent
        @lang('layouts/admin.add_whitelist')
    @endcomponent
@endcomponent

@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2', 'isLink' => 1, 'id' => 'm_starmap'])
    @slot('title')
        @lang('layouts/admin.starmap')
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_starmap_systems_list'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            circle-o-notch
        @endcomponent
        @lang('layouts/admin.systems')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_starmap_celestialobject_list'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            circle-o-notch
        @endcomponent
        @lang('layouts/admin.celestialobjects')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => '#'])
        @slot('options')
            onclick="event.preventDefault(); document.getElementById('download-starmap').submit();"
        @endslot

        @component('components.forms.form', ['id' => 'download-starmap', 'action' => route('admin_starmap_systems_download'), 'method' => 'POST', 'class' => 'd-none'])
            <input name="_method" type="hidden" value="POST">
        @endcomponent

        @component('components.elements.icon', ['class' => 'mr-2'])
            repeat
        @endcomponent
        @lang('layouts/admin.download_starmap')
    @endcomponent
@endcomponent

@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2', 'isLink' => 1, 'id' => 'm_ships'])
    @slot('title')
        @lang('layouts/admin.starmap')
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'admin_ships_list'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            rocket
        @endcomponent
        @lang('layouts/admin.ships')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => '#'])
        @slot('options')
            onclick="event.preventDefault(); document.getElementById('download-ships').submit();
        @endslot

        @component('components.forms.form', ['id' => 'download-ships', 'action' => route('admin_ships_download'), 'method' => 'POST', 'class' => 'd-none'])
            <input name="_method" type="hidden" value="POST">
        @endcomponent

        @component('components.elements.icon', ['class' => 'mr-2'])
            repeat
        @endcomponent
        @lang('layouts/admin.download_ships')
    @endcomponent
@endcomponent