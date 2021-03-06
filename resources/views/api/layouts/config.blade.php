{{-- Page Title --}}
@section('P__title')
    @parent
    @hasSection('title')
        @yield('title') - Star Citizen Wiki Api
    @else
        Star Citizen Wiki Api
    @endif
@endsection


{{-- Head --}}
@section('head__content')
    @parent
    <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
@endsection


{{-- Body --}}
@section('sidebar--class', 'col-md-4 col-lg-3 d-none d-md-flex flex-column')


{{-- Sidebar Content --}}
@section('sidebar__content')
    @include('api.menu.main')
@endsection

@section('sidebar__pre')
    @parent
    <a href="{{ url('/') }}">
        <img src="{{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}"
             class="d-block mx-auto my-5 img-fluid"
             style="max-width: 169px;">
    </a>
@endsection

@section('sidebar__after')
    @parent
    @include('components.sidebar_imprint')
@endsection


{{-- Main Content --}}
@section('main--class', 'col-md-8 col-lg-9')

@section('topNav--class', 'navbar-expand-md bg-blue-grey')

@section('topNav__content')
    @include('api.menu.login_logout')
@endsection


@section('body__after')
    @parent
    <script src="{{ mix('/js/app.js') }}"></script>
@endsection