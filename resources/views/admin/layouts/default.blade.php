@extends('layouts.default')

@include('admin.layouts.layout_config')

{{-- Body --}}
@section('sidebar__pre')
    @parent
    @component('components.elements.element', ['type' => 'a', 'class' => 'w-100'])
        @slot('options')
            href="/"
        @endslot

        @component('components.elements.img', ['class' => 'd-block mx-auto my-5 img-fluid'])
            @slot('options')
                style="max-width: 100px;"
            @endslot

            {{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}
        @endcomponent
    @endcomponent
@endsection

@section('sidebar__after')
    @parent
    @component('components.navs.sidebar_section', ['contentClass' => 'mb-0'])
        @slot('options')
            style="position: absolute; bottom: 0; margin-bottom: 0 !important;"
        @endslot

        @component('components.navs.nav_element', ['route' => 'https://star-citizen.wiki/', 'contentClass' => 'text-white'])
            Star Citizen Wiki - &copy; {{ date("Y") }}
        @endcomponent
    @endcomponent
@endsection
