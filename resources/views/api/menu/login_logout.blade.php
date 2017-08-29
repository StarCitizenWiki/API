@if (Auth::guest())
    @component('components.navs.nav_element', ['route' => route('auth_login')])
        @component('components.elements.icon')
            sign-in
        @endcomponent
        @lang('Login')
    @endcomponent
@else
    @component('components.navs.nav_element', [
        'route' => route('account'),
        'class' => 'mr-2',
    ])
        @component('components.elements.icon', ['class' => 'mr-1'])
            user
        @endcomponent
        @lang('Account')
    @endcomponent

    @component('components.navs.nav_element', ['route' => route('auth_logout')])
        @slot('options')
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        @endslot

        @component('components.forms.form', [
            'id' => 'logout-form',
            'action' => route('auth_logout'),
            'class' => 'd-none',
        ])
        @endcomponent

        @component('components.elements.icon', ['class' => 'mr-1'])
            sign-out
        @endcomponent
        @lang('Logout')
    @endcomponent
@endif