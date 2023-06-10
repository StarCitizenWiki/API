@component('components.navs.nav_element', [
    'route' => 'https://github.com/StarCitizenWiki/API'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon', ['type' => 'fab'])
                github
            @endcomponent
        </div>
        <div class="col">
            @lang('Quellcode')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://docs.star-citizen.wiki/'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                cloud
            @endcomponent
        </div>
        <div class="col">
            @lang('RSI API')
        </div>
    </div>
@endcomponent


<?php
/*
@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                question-circle
            @endcomponent
        </div>
        <div class="col">
            @lang('FAQ')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                rocket
            @endcomponent
        </div>
        <div class="col">
            @lang('Wiki API')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'contentClass' => 'disabled',
    'route' => '-'
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                image
            @endcomponent
        </div>
        <div class="col">
            @lang('Medien')
        </div>
    </div>
@endcomponent
*/
?>