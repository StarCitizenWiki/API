@component('components.navs.nav_element', [
    'route' => route('admin.url.list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                link
            @endcomponent
        </div>
        <div class="col">
            @lang('ShortUrls')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin.url.add_form'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                plus-circle
            @endcomponent
        </div>
        <div class="col">
            @lang('URL hinzufügen')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin.url.whitelist.list'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                list
            @endcomponent
        </div>
        <div class="col">
            @lang('Erlaubte Domains')
        </div>
    </div>
@endcomponent

@component('components.navs.nav_element', [
    'route' => route('admin.url.whitelist.add_form'),
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                plus-circle
            @endcomponent
        </div>
        <div class="col">
            @lang('Domain hinzufügen')
        </div>
    </div>
@endcomponent