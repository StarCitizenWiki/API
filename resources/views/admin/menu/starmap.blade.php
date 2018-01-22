@component('components.navs.nav_element', [
    'route' => route('admin_starmap_systems_list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                circle-notch
            @endcomponent
        </div>
        <div class="col">
            @lang('Systeme')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin_starmap_celestialobject_list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                circle-notch
            @endcomponent
        </div>
        <div class="col">
            @lang('Celestial Objects')
        </div>
    </div>
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
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                repeat
            @endcomponent
        </div>
        <div class="col">
            @lang('Starmapdaten laden')
        </div>
    </div>
@endcomponent