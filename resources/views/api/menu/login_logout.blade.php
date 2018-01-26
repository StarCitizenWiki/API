@if (Auth::guest())
    @component('components.navs.nav_element', ['route' => route('auth.login')])
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
            user-circle
        @endcomponent
        @lang('Account')
    @endcomponent

    @component('components.navs.nav_element', ['route' => route('auth.logout')])
        @slot('options')
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        @endslot

        @component('components.forms.form', [
            'id' => 'logout-form',
            'action' => route('auth.logout'),
            'class' => 'd-none',
        ])
        @endcomponent

        @component('components.elements.icon', ['class' => 'mr-1'])
            sign-out
        @endcomponent
        @lang('Logout')
    @endcomponent
@endif