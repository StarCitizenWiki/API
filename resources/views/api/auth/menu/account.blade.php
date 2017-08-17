@component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'account'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        home
    @endcomponent
    Home
@endcomponent

@component('components.navs.nav_element', ['contentClass' => 'text-white', 'route' => 'account_edit_form'])
    @component('components.elements.icon', ['class' => 'mr-2'])
        pencil
    @endcomponent
    Bearbeiten
@endcomponent