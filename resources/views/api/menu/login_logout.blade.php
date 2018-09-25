@auth
    @component('components.navs.nav_element', [
    'route' => route('web.user.account.show'),
    'class' => 'mr-2',
])
        @component('components.elements.icon', ['class' => 'mr-1'])
            user-circle
        @endcomponent
        @lang('Account')
    @endcomponent

    @component('components.navs.nav_element', ['route' => route('web.user.auth.logout')])
        @slot('options')
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        @endslot

        @component('components.forms.form', [
            'id' => 'logout-form',
            'action' => route('web.user.auth.logout'),
            'class' => 'd-none',
        ])
        @endcomponent

        @component('components.elements.icon', ['class' => 'mr-1'])
            sign-out
        @endcomponent
        @lang('Logout')
    @endcomponent
@else
    @component('components.navs.nav_element', ['route' => route('web.user.auth.login')])
        @component('components.elements.icon')
            sign-in
        @endcomponent
        @lang('Login')
    @endcomponent
@endauth

@component('components.navs.nav_element', ['contentClass' => 'small'])
    @slot('options')
        style="padding-top: 0.7rem; cursor: default"
    @endslot
    v{{ config('app.version') }}
@endcomponent