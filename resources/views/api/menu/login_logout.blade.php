@auth
    @component('components.navs.nav_element', [
        'route' => route('web.user.dashboard')
    ])
        @lang('Dashboard')
    @endcomponent
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="admin_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ Auth::user()->username }}
        </a>
        <div class="dropdown-menu" aria-labelledby="admin_dropdown">
            <a class="dropdown-item" href="{{ route('web.user.account.index') }}">
                @component('components.elements.icon', ['class' => 'mr-1'])
                    user-circle
                @endcomponent
                @lang('Account')
            </a>
            <button class="dropdown-item" id="darkmode-toggle">Dark-Mode Umschalten</button>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                @component('components.forms.form', [
                    'id' => 'logout-form',
                    'action' => route('web.user.auth.logout'),
                    'class' => 'd-none',
                ])
                @endcomponent
                @component('components.elements.icon', ['class' => 'mr-1'])
                    sign-out-alt
                @endcomponent
                @lang('Logout')
            </a>
        </div>
    </li>
    @component('components.navs.nav_element', ['contentClass' => 'small'])
        @slot('options')
            style="padding-top: 0.7rem; cursor: default"
        @endslot
        v{{ config('app.version') }}
    @endcomponent
@else
    @component('components.navs.nav_element', ['route' => route('web.user.auth.login')])
        @component('components.elements.icon')
            sign-in-alt
        @endcomponent
        @lang('Login')
    @endcomponent
@endauth