@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2'])
    @slot('title')
        @lang('api/index.documentation')
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'api_faq'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            question-circle
        @endcomponent
        @lang('api/index.faq')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => '-'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            cloud
        @endcomponent
        @lang('api/index.rsi_api')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => '-'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            rocket
        @endcomponent
        @lang('api/index.wiki_api')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => '-'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            link
        @endcomponent
        @lang('api/index.url_api')
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => '-'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            image
        @endcomponent
        @lang('api/index.media_api')
    @endcomponent
@endcomponent

@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2'])
    @slot('title')
        @lang('api/index.follow_us')
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'https://star-citizen.wiki/'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            globe
        @endcomponent
        star-citizen.wiki
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'https://twitter.com/SC_Wiki'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            twitter
        @endcomponent
        SC_Wiki
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'https://facebook.com/StarCitizenWiki'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            facebook-square
        @endcomponent
        StarCitizenWiki
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'https://robertsspaceindustries.com/orgs/WIKI'])
        @component('components.elements.icon', ['class' => 'mr-2'])
            building-o
        @endcomponent
        WIKI
    @endcomponent
@endcomponent