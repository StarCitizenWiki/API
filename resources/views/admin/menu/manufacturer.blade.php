@component('components.navs.nav_element', [
    'route' => route('admin.manufacturers.list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                industry
            @endcomponent
        </div>
        <div class="col">
            @lang('Manufacturer')
        </div>
    </div>
@endcomponent
