@component('components.navs.nav_element', [
    'route' => route('admin_starmap_systems_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        circle-o-notch
    @endcomponent
    __LOC__Systems
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin_starmap_celestialobject_list'),
])
    @component('components.elements.icon', ['class' => 'mr-2'])
        circle-o-notch
    @endcomponent
    __LOC__CelestialObjects
@endcomponent

@component('components.navs.nav_element', [
    'route' => '#',
])
    @slot('options')
        onclick="event.preventDefault(); document.getElementById('download-starmap').submit();"
    @endslot

    @component('components.forms.form', [
        'id' => 'download-starmap',
        'action' => route('admin_starmap_systems_download'),
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
    __LOC__Download_Starmap
@endcomponent