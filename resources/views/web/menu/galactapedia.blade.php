@component('components.navs.nav_element', [
    'route' => route('web.starcitizen.galactapedia.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                newspaper
            @endcomponent
        </div>
        <div class="col">
            @lang('Galactapedia Artikel')
        </div>
    </div>
@endcomponent
