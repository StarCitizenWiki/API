@extends('layouts.base')

@section('body__content')
    @component('components.elements.container', ['type' => 'fluid'])
        @slot('class')
{{--    --}}mvh-100 @yield('container--class')
        @endslot

        @slot('id')
{{--    --}}@yield('container--id')
        @endslot

        @slot('options')
{{--    --}}@yield('container--options')
        @endslot

        {{-- Content --}}
        <div class="row">
        @component('components.navs.top_nav')
            @slot('class')
{{--        --}}col-12 pr-4 @yield('topNav--class')
            @endslot

            @slot('title')
{{--        --}}@yield('topNav__title')
            @endslot

            @slot('titleLink')
{{--        --}}@yield('topNav__titleLink')
            @endslot

            @slot('contentClass')
{{--        --}}@yield('topNav__content--class')
            @endslot

            {{-- Slot Content --}}
{{--    --}}@yield('topNav__content')
        @endcomponent
        </div>

        <div class="row @yield('containerRow--class')" id="@yield('containerRow--id')" @yield('containerRow--options')>
            <main class="col-12 @yield('main--class')" id="@yield('main--id')" @yield('main--options')>
{{--        --}}@yield('P__content')
            </main>
        </div>
    @endcomponent
@endsection