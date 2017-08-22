@component('components.navs.nav_element', [
    'route' => route('account'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        home
    @endcomponent
    __LOC__Home
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('account_edit_form'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        pencil
    @endcomponent
    __LOC__Bearbeiten
@endcomponent

@unless(Auth::user()->isBlacklisted())
    @component('components.navs.nav_element', [
        'route' => route('account_delete_form'),
    ])
        @component('components.elements.icon', ['class' => 'mr-2'])
            trash
        @endcomponent
        __LOC__Löschen
    @endcomponent
@endunless