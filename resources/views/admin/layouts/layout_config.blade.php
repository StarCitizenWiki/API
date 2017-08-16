{{-- Page Title --}}
@section('P__title')
    @parent
    @hasSection('title')
        @yield('title') - Star Citizen Wiki API Admin
    @else
        Star Citizen Wiki API Admin
    @endif
@endsection


{{-- Head --}}
@section('head__content')
    @parent
    <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
@endsection


{{-- Body --}}
@section('sidebarRow--class', 'flex-column')

{{-- Sidebar Content --}}
@section('sidebar__content')
    @include('admin.menu')
@endsection

{{-- Main Content --}}
@section('topNav--class', 'bg-blue-grey')

@section('topNav__content')
    @unless(Auth::guest())
        @component('components.navs.nav_element', ['route' => 'admin_logout'])
            @slot('options')
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            @endslot

            @component('components.forms.form', ['id' => 'logout-form', 'action' => route('admin_logout'), 'method' => 'POST', 'class' => 'd-none'])
            @endcomponent

            @component('components.elements.icon', ['class' => 'mr-1'])
                sign-out
            @endcomponent
            @lang('layouts/app.logout')
        @endcomponent
    @endunless
@endsection


@section('body__after')
    @parent
    <script src="{{ mix('/js/app.js') }}"></script>
@endsection