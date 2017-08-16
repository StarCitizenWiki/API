@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2'])
    @slot('title')
        Account
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'account'])
        {{-- Slot --}}
        @component('components.elements.icon', ['class' => 'mr-2'])
            home
        @endcomponent
        Home
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'account_edit_form'])
        {{-- Slot --}}
        @component('components.elements.icon', ['class' => 'mr-2'])
            pencil
        @endcomponent
        Bearbeiten
    @endcomponent
@endcomponent

@component('components.navs.sidebar_section', ['titleClass' => 'text-muted', 'contentClass' => 'pl-2'])
    @slot('title')
        ShortURLs
    @endslot

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'account_urls_list'])
        {{-- Slot --}}
        @component('components.elements.icon', ['class' => 'mr-2'])
            bars
        @endcomponent
        ShortURLs
    @endcomponent

    @component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'account_urls_add_form'])
        {{-- Slot --}}
        @component('components.elements.icon', ['class' => 'mr-2'])
            plus
        @endcomponent
        Erstellen
    @endcomponent
@endcomponent