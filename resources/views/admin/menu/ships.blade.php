@component('components.navs.nav_element', [
    'route' => route('admin_ships_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        rocket
    @endcomponent
    @lang('Ships')
@endcomponent

@component('components.navs.nav_element', [
    'route' => '#',
])
    @slot('options')
        onclick="event.preventDefault(); document.getElementById('download-ships').submit();
    @endslot

    @component('components.forms.form', [
        'id' => 'download-ships',
        'action' => route('admin_ships_download'),
        'method' => 'POST',
        'class' => 'd-none',
    ])
        @component('components.elements.element', ['type' => 'input'])
            @slot('options')
                name="_method" type="hidden" value="POST"
            @endslot
        @endcomponent
    @endcomponent

    @component('components.elements.icon', ['class' => 'mr-2'])
        repeat
    @endcomponent
    @lang('Schiffsdaten laden')
@endcomponent