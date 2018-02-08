@component('components.navs.nav_element', [
    'route' => route('account'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                home
            @endcomponent
        </div>
        <div class="col">
            @lang('Account')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('account.edit_form'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                pencil
            @endcomponent
        </div>
        <div class="col">
            @lang('Bearbeiten')
        </div>
    </div>
@endcomponent

@unless(Auth::user()->isBlocked())
    @component('components.navs.nav_element', [
        'route' => route('account.delete_form'),
    ])
        <div class="row">
            <div class="col-1">
                @component('components.elements.icon')
                    trash
                @endcomponent
            </div>
            <div class="col">
                @lang('Löschen')
            </div>
        </div>
    @endcomponent
@endunless