@component('components.navs.nav_element', [
    'route' => 'mailto:'.config('mail.from.address'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                envelope
            @endcomponent
        </div>
        <div class="col">
            @lang('Mail')
        </div>
    </div>
@endcomponent
