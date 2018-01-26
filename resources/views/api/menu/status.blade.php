@component('components.navs.nav_element', [
    'route' => route('api.status'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                dot-circle
            @endcomponent
        </div>
        <div class="col">
            @lang('Api Status')
        </div>
    </div>
@endcomponent