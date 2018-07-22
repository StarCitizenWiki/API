@component('components.navs.nav_element', [
    'route' => route('web.admin.starcitizen.production_statuses.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                clipboard-check
            @endcomponent
        </div>
        <div class="col">
            @lang('Production Statuses')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('web.admin.starcitizen.production_notes.index'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                sticky-note
            @endcomponent
        </div>
        <div class="col">
            @lang('Production Notes')
        </div>
    </div>
@endcomponent
