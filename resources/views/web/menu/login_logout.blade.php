@component('components.navs.nav_element', [
    #'contentClass' => 'small',
    'route' => 'https://github.com/StarCitizenWiki/API',
])
    v{{ config('app.version') }}
@endcomponent
@component('components.navs.nav_element', ['class' => 'mr-3'])
    <div class="nav-item dropdown" id="theme-selector">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
            @component('components.elements.icon', ['class' => 'mr-1'])
                palette
            @endcomponent
            Themes
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" id="darkmode" href="#">
                @component('components.elements.icon', ['class' => 'mr-1'])
                    moon
                @endcomponent
                @lang('Dunkel modus')
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" id="light" href="#">
                @component('components.elements.icon', ['class' => 'mr-1'])
                    sun
                @endcomponent
                @lang('Heller modus')
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" id="spacetheme" href="#">
                @component('components.elements.icon', ['class' => 'mr-1'])
                    user-astronaut
                @endcomponent
                SpaceTheme (Recolor)
            </a>
        </div>
    </div>
@endcomponent
@auth
    <li class="nav-item dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
            @component('components.elements.icon', ['class' => 'mr-1'])
                user
            @endcomponent
            {{ Auth::user()->username }}
        </button>
        <div class="dropdown-menu" aria-labelledby="admin_dropdown">
            <a class="dropdown-item" href="{{ route('web.account.index') }}">
                @component('components.elements.icon', ['class' => 'mr-1'])
                    user-circle
                @endcomponent
                @lang('Account')
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                @component('components.forms.form', [
                    'id' => 'logout-form',
                    'action' => route('web.auth.logout'),
                    'class' => 'd-none',
                ])
                @endcomponent
                @component('components.elements.icon', ['class' => 'mr-1'])
                    sign-out-alt
                @endcomponent
                @lang('Abmelden')
            </a>
        </div>
    </li>
@else
    <div class="btn btn-secondary">
        @component('components.navs.nav_element', ['route' => route('web.auth.login')])
            @component('components.elements.icon')
                sign-in-alt
            @endcomponent
            @lang('Anmelden')
        @endcomponent
    </div>
@endauth