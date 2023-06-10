@component('components.navs.nav_element', [
    'route' => 'https://star-citizen.wiki/',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                globe
            @endcomponent
        </div>
        <div class="col">
            @lang('Wiki')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://discord.star-citizen.wiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon', ['type' => 'fab'])
                discord
            @endcomponent
        </div>
        <div class="col">
            @lang('Discord')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://www.youtube.com/@StarCitizenWiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon', ['type' => 'fab'])
                youtube
            @endcomponent
        </div>
        <div class="col">
            @lang('YouTube')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://mastodon.star-citizen.wiki/@StarCitizenWiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon', ['type' => 'fab'])
                mastodon
            @endcomponent
        </div>
        <div class="col">
            @lang('Mastodon')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://facebook.com/StarCitizenWiki',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon', ['type' => 'fab'])
                facebook
            @endcomponent
        </div>
        <div class="col">
            @lang('Facebook')
        </div>
    </div>
@endcomponent


@component('components.navs.nav_element', [
    'route' => 'https://robertsspaceindustries.com/orgs/WIKI',
])
    <div class="row">
        <div class="col-1">
            @component('components.elements.icon')
                building
            @endcomponent
        </div>
        <div class="col">
            @lang('Wiki Orga')
        </div>
    </div>
@endcomponent