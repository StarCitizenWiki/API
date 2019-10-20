@component('components.navs.nav_element', [
    'route' => route('web.user.transcripts.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                comment-alt
            @endcomponent
        </div>
        <div class="col">
            @lang('Transkripte')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.transcripts.create'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                plus
            @endcomponent
        </div>
        <div class="col">
            @lang('Hinzufügen')
        </div>
    </div>
@endcomponent