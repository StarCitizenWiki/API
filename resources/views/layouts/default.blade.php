@extends('layouts.base')

@section('body--class', 'bg-light')

@section('body__content')
    <script>(()=>{'on'===window.localStorage.getItem("darkmode")&&document.body.parentElement.classList.add("darkmode");})();</script>
    @component('components.elements.container', ['type' => 'fluid'])
        {{-- Container Config --}}
        @slot('class')
            @yield('container--class')
        @endslot

        @slot('id')
            @yield('container--id')
        @endslot

        @slot('options')
            @yield('container--options')
        @endslot

        {{-- Container Content --}}
        <div class="row @yield('containerRow--class')" id="@yield('containerRow--id')" @yield('containerRow--options')>
            {{-- Sidebar --}}
            <aside class="col-12 col-xl-2 bg-dark nav-dark mvh-100 @yield('sidebar--class')" id="@yield('sidebar--id')" @yield('sidebar--options')>
                @yield('sidebar__pre')
                @yield('sidebar__content')
                @yield('sidebar__after')
            </aside>

            {{-- Content --}}
            <main class="col-12 col-xl-10 @yield('main--class')" id="@yield('main--id')" @yield('main--options')>
                <div class="row @yield('topNavRow--class')" @yield('topNavRow--options')>
                    {{-- Top Nav --}}
                    @component('components.navs.top_nav')
                        @slot('class')
                            @yield('topNav--class')
                        @endslot

                        @slot('title')
                            @yield('topNav__title')
                        @endslot

                        @slot('titleClass')
                            @yield('topNav__title--class')
                        @endslot

                        @slot('titleLink')
                            @yield('topNav__titleLink')
                        @endslot

                        @slot('contentClass')
                            @yield('topNav__content--class')
                        @endslot

                        {{-- Slot Content --}}
                        @yield('topNav__content')
                    @endcomponent
                </div>
                {{-- Page Content --}}
                <div class="row @yield('contentRow--class')" id="@yield('contentRow--id')" @yield('contentRow--options')>
                    @yield('P__content')
                </div>
            </main>
        </div>
    @endcomponent
@endsection