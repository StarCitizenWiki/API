@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizen.production-statuses.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                clipboard-check
            @endcomponent
        </div>
        <div class="col">
            @lang('Produktionsstatus')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.user.starcitizen.production-notes.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                sticky-note
            @endcomponent
        </div>
        <div class="col">
            @lang('Produktionsnotizen')
        </div>
    </div>
@endcomponent
