@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizen.galactapedia.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                comment-alt
            @endcomponent
        </div>
        <div class="col">
            @lang('Galactapedia Artikel')
        </div>
    </div>
@endcomponent
