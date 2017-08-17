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