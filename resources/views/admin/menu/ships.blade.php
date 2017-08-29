@component('components.navs.nav_element', [
    'route' => route('admin_ships_list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                rocket
            @endcomponent
        </div>
        <div class="col">
            @lang('Ships')
        </div>
    </div>
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
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                repeat
            @endcomponent
        </div>
        <div class="col">
            @lang('Schiffsdaten laden')
        </div>
    </div>
@endcomponent