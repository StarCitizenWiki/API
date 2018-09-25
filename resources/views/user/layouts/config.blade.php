{{-- Page Title --}}
@section('P__title')
    @parent
    @hasSection('title')
        @yield('title') - Star Citizen Wiki Api Admin
    @else
        Star Citizen Wiki Api Admin
    @endif
@endsection


{{-- Head --}}
@section('head__content')
    @parent
    <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
@endsection


{{-- Body --}}
@section('sidebar--class', 'd-none d-md-flex flex-column')


{{-- Sidebar Content --}}
@section('sidebar__content')
    @include('user.menu.main')
@endsection

@section('sidebar__pre')
    @parent
    <a href="{{ route('web.user.dashboard') }}">
        <img src="{{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}"
             class="d-block mx-auto my-5 img-fluid"
             style="max-width: 100px;">
    </a>
@endsection

@section('sidebar__after')
    @parent
    @include('components.sidebar_imprint')
@endsection


{{-- Main Content --}}
@section('topNav--class', 'bg-blue-grey')

@section('topNav__content')
    @auth
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="admin_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ Auth::user()->username }}
            </a>
            <div class="dropdown-menu" aria-labelledby="admin_dropdown">
                <a class="dropdown-item" href="{{ route('web.user.account.show') }}">
                    @component('components.elements.icon', ['class' => 'mr-1'])
                        user
                    @endcomponent
                    @lang('Account')
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
                sign-in
            @endcomponent
            @lang('Login')
        @endcomponent
    @endauth
@endsection


@section('body__after')
    @parent
    <script src="{{ mix('/js/app.js') }}"></script>
@endsection