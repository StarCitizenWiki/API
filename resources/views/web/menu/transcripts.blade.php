@component('components.navs.nav_element', [
    'route' => route('web.transcripts.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                scroll
            @endcomponent
        </div>
        <div class="col">
            @lang('Transkripte')
        </div>
    </div>
@endcomponent

