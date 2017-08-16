@extends('layouts.base')

@section('body__content')
    @component('components.elements.container', ['type' => 'fluid'])
        {{-- Container Config --}}
        @slot('class')
{{--    --}}mvh-100 @yield('container--class')
        @endslot

        @slot('id')
{{--    --}}@yield('container--id')
        @endslot

        @slot('options')
{{--    --}}@yield('container--options')
        @endslot

        {{-- Container Content --}}
        <div class="row @yield('containerRow--class')" id="@yield('containerRow--id')" @yield('containerRow--options')>
            {{-- Sidebar --}}
            <aside class="col-12 col-md-2 bg-dark pb-4 mvh-100 @yield('sidebar--class')" id="@yield('sidebar--id')" @yield('sidebar--options')>
                <div class="row @yield('sidebarRow--class')" id="@yield('sidebarRow--id')" @yield('sidebarRow--options')>
{{--            --}}@yield('sidebar__pre')
{{--            --}}@yield('sidebar__content')
{{--            --}}@yield('sidebar__after')
                </div>
            </aside>

            {{-- Content --}}
            <main class="col-12 col-md-10 @yield('main--class')" id="@yield('main--id')" @yield('main--options')>
                <div class="row @yield('contentRow--class')" id="@yield('contentRow--id')" @yield('contentRow--options')>
                    {{-- Top Nav --}}
                    @component('components.navs.top_nav')
                        @slot('class')
{{--                    --}}col-12 pr-4 @yield('topNav--class')
                        @endslot

                        @slot('title')
{{--                    --}}@yield('topNav__title')
                        @endslot

                        @slot('titleLink')
{{--                    --}}@yield('topNav__titleLink')
                        @endslot

                        @slot('contentClass')
{{--                    --}}@yield('topNav__content--class')
                        @endslot

                        {{-- Slot Content --}}
{{--                --}}@yield('topNav__content')
                    @endcomponent

                    {{-- Page Content --}}
{{--            --}}@yield('P__content')
                </div>
            </main>
        </div>
    @endcomponent
@endsection