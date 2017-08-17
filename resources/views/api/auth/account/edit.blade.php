@extends('api.layouts.default')

{{-- Page Title --}}
@section('title')
    @lang('auth/account/edit.header')
@endsection

@section('sidebar__content')
    @include('api.auth.menu.main')
@endsection

@section('topNav__content')
    @component('components.elements.div', ['class' => 'nav flex-column d-sm-flex d-md-none'])
        @include('api.auth.menu.main')
    @endcomponent
@endsection

@section('P__content')
    @component('components.elements.container', [
        'type' => 'fluid',
        'class' => 'mt-5'
    ])
        {{-- Row --}}
        @component('components.elements.div', ['class' => 'row flex-column mt-md-5'])
            {{-- Wrap Div --}}
            @component('components.elements.div', ['class' => 'col-12 col-md-4 mx-auto d-flex flex-column mb-5'])

                @component('components.heading', [
                    'class' => 'mb-4',
                    'hideImage' => 1
                ])
                    Edit Account
                @endcomponent

                <form role="form" method="POST" action="{{ route('account_update') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="PATCH">
                    <div class="form-group">
                        <label for="name" aria-label="Name">@lang('auth/account/edit.name'):</label>
                        <input type="text" class="form-control" id="name" name="name" aria-labelledby="name" tabindex="1" value="{{ $user->name }}" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="email" aria-label="E-Mail">@lang('auth/account/edit.email'):</label>
                        <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="2" data-minlength="3" value="{{ $user->email }}">
                    </div>
                    <div class="form-group">
                        <label for="password" aria-label="Passwort">@lang('auth/account/edit.password'):</label>
                        <input type="password" class="form-control" id="password" name="password" aria-labelledby="password" tabindex="3" data-minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" aria-label="Passwort">@lang('auth/account/edit.password_confirmation'):</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" aria-labelledby="password" tabindex="4" data-minlength="8">
                    </div>
                    <button type="submit" class="btn btn-warning my-3">@lang('auth/account/edit.edit')</button>
                </form>
                @unless($user->isBlacklisted())
                    <hr>
                    <h4 class="my-4">Danger Zone:</h4>
                    <form role="form" method="POST" action="{{ route('account_delete') }}">
                        {{ csrf_field() }}
                        <input name="_method" type="hidden" value="DELETE">
                        <button class="btn btn-danger" type="submit">
                            @lang('auth/account/index.delete')
                        </button>
                    </form>
                @endunless
            @endcomponent
        @endcomponent
    @endcomponent
@endsection