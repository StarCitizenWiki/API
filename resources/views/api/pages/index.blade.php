@extends('api.layouts.default')

{{-- Page Title --}}
@section('title')
    @lang('api/index.header')
@endsection

{{-- Page Content --}}
@section('P__content')
    @component('components.elements.container', ['type' => 'fluid'])
        {{-- Content --}}
        @component('components.elements.div', ['class' => 'row'])
            @component('components.elements.div', ['class' => 'col-12 col-md-4 mx-auto'])
                @component('components.heading', ['class' => 'my-5 text-center', ['contentClass' => 'mt-5']])
                    Star Citizen Wiki API
                @endcomponent

                @component('components.elements.div', ['class' => 'alert alert-danger'])
                    @lang('api/index.notice')
                @endcomponent

                @include('components.errors')

                @if (Auth::guest())
                    @component('components.elements.element', ['type' => 'form'])
                        @slot('options')
                            method="POST" action="{{ route('auth_register') }}"
                        @endslot

                        {{ csrf_field() }}
                        @component('components.elements.div', ['class' => 'input-group input-group-lg mx-auto'])
                            <input id="email" type="email" class="center-block form-control input-lg" name="email" value="{{ old('email') }}" required placeholder="@lang('api/index.email')">
                            @component('components.elements.element', ['type' => 'span', 'class' => 'input-group-btn'])
                                <button class="btn btn-lg btn-primary" type="submit">@lang('api/index.request_api_key')</button>
                            @endcomponent
                        @endcomponent
                    @endcomponent
                @else
                    @component('components.elements.element', ['type' => 'p', 'class' => 'text-center'])
                        @component('components.elements.element', ['type' => 'b'])
                            @lang('api/index.api_key'):
                        @endcomponent
                        &nbsp;
                        @component('components.elements.element', ['type' => 'code'])
                            {{ Auth::user()->api_token }}
                        @endcomponent
                    @endcomponent
                @endif
            @endcomponent
        @endcomponent

        @component('components.elements.div', ['class' => 'row mt-5'])
            @component('components.elements.div', ['class' => 'col-12 col-md-6 mt-5 mx-auto'])
                @component('components.elements.div', ['class' => 'panel panel-default'])
                    @component('components.elements.div', ['class' => 'panel-heading'])
                        @component('components.elements.element', ['type' => 'h4'])
                            @component('components.elements.icon')
                                pencil
                            @endcomponent
                            @lang('api/index.the_project')
                        @endcomponent
                    @endcomponent

                    @component('components.elements.div', ['class' => 'panel-body mt-3 mr-5'])
                        @lang('api/index.about')
                        <a href="mailto:info@star-citizen.wiki" class="font-italic text-dark">@lang('api/index.write')</a> @lang('api/index.about_2')
                        <a href="ts3server://ts.star-citizen.wiki" class="font-italic text-dark">@lang('api/index.teamspeak_server')</a>!
                    @endcomponent
                @endcomponent
            @endcomponent
        @endcomponent
    @endcomponent
@endsection