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
    @include('admin.menu.main')
@endsection

@section('sidebar__pre')
    @parent
    <a href="{{ route('web.admin.dashboard') }}">
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

@section('topNav__title', 'API Admin')
@section('topNav__title--class', 'd-md-none')


@section('topNav__content')
    @unless(Auth::guest())
        @component('components.navs.nav_element', ['route' => route('web.admin.auth.logout')])
            @slot('options')
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            @endslot

            @component('components.forms.form', [
                'id' => 'logout-form',
                'action' => route('web.admin.auth.logout'),
                'class' => 'd-none',
            ])
            @endcomponent

            @component('components.elements.icon', ['class' => 'mr-1'])
                sign-out
            @endcomponent
            @lang('Logout')
        @endcomponent
    @endunless

    <div class="nav d-flex d-md-none flex-row flex-lg-column">
        @include('admin.menu.main')
    </div>
@endsection


@section('body__after')
    @parent
    <script src="{{ mix('/js/app.js') }}"></script>
@endsection