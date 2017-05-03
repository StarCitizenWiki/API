@extends('layouts.app')
@section('title')
    @lang('auth/account/index.header')
@endsection

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-6 offset-md-3 mt-5">
                <div>
                    <h4>@lang('auth/account/index.api_key'):</h4>
                    <p class="">
                        <code>
                            {{ $user->api_token }}
                        </code>
                    </p>
                </div>

                <div class="mt-4">
                    <h4>@lang('auth/account/index.requests_per_minute'):</h4>
                    <p class="">
                        <code>
                            {{ $user->requests_per_minute }}
                        </code>
                    </p>
                </div>

                <div class="mt-4 mb-5">
                    <h4>@lang('auth/account/index.email'):</h4>
                    <p class="">
                        <code>
                            {{ $user->email }}
                        </code>
                    </p>
                </div>

                <hr>

                <div class="mt-4 mb-4">
                    <h4 class="mb-4">Danger-Zone:</h4>
                    <a href="{{ route('account_edit_form') }}" class="btn btn-warning d-inline-block mr-2">
                        @lang('auth/account/index.edit')
                    </a>
                    @unless($user->isBlacklisted())
                    <form role="form" method="POST" action="{{ route('account_delete') }}" class="d-inline-block pull-right">
                        {{ csrf_field() }}
                        <input name="_method" type="hidden" value="DELETE">
                        <button class="btn btn-danger" type="submit">
                            @lang('auth/account/index.delete')
                        </button>
                    </form>
                    @endunless
                </div>
            </div>
        </div>
    </div>
@endsection


