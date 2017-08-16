@extends('api.layouts.default')

{{-- Page Title --}}
@section('title')
    @lang('auth/account/index.header')
@endsection

@section('sidebar__content')
    @parent
    @include('api.auth.account.menu')
@endsection

@section('P__content')
    @component('components.elements.container', ['type' => 'fluid', 'class' => 'mt-5'])
        {{-- Row --}}
        @component('components.elements.div', ['class' => 'row flex-column mt-5'])
            {{-- Wrap Div --}}
            @component('components.elements.div', ['class' => 'col-12 col-md-4 mx-auto d-flex flex-column mb-5'])
                @component('components.heading', ['hideImage' => 1, 'class' => 'mb-4'])
                    Account
                @endcomponent

                {{-- Element --}}
                @component('components.elements.div', ['class' => 'mt-4'])
                    {{-- Content --}}
                    @component('components.elements.element', ['type' => 'h4'])
                        @lang('auth/account/index.api_key'):
                    @endcomponent
                    @component('components.elements.element', ['type' => 'code'])
                        {{ $user->api_token }}
                    @endcomponent
                @endcomponent

                {{-- Element --}}
                @component('components.elements.div', ['class' => 'mt-4'])
                    {{-- Content --}}
                    @component('components.elements.element', ['type' => 'h4'])
                        @lang('auth/account/index.requests_per_minute'):
                    @endcomponent
                    @component('components.elements.element', ['type' => 'code'])
                        {{ $user->requests_per_minute }}
                    @endcomponent
                @endcomponent

                {{-- Element --}}
                @component('components.elements.div', ['class' => 'mt-4'])
                    {{-- Content --}}
                    @component('components.elements.element', ['type' => 'h4'])
                        @lang('auth/account/index.email'):
                    @endcomponent
                    @component('components.elements.element', ['type' => 'code'])
                        {{ $user->email }}
                    @endcomponent
                @endcomponent
            @endcomponent
        @endcomponent
    @endcomponent
@endsection