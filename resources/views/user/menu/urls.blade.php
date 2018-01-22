@component('components.navs.nav_element', [
    'route' => route('account_url_list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                list
            @endcomponent
        </div>
        <div class="col">
            @lang('ShortUrls')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('account_url_add_form'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                plus
            @endcomponent
        </div>
        <div class="col">
            @lang('Erstellen')
        </div>
    </div>
@endcomponent