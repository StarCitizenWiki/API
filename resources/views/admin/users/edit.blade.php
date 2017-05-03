@extends('layouts.admin')
@section('title', 'Edit User')
@section('header')
    <style>
        .display-5 {
            font-size: 2.5rem;
        }

        .date {
            white-space: nowrap;
        }

        .stack {
            font-size: 0.85em;
        }

        .date {
            min-width: 75px;
        }

        .text {
            word-break: break-all;
        }

        a.llv-active {
            z-index: 2;
            background-color: #f5f5f5;
            border-color: #777;
        }
    </style>
@endsection

@section('content')
    <div class="col-12 col-md-4 mx-auto">
        @if ($user->isBlacklisted())
            @component('components.alert')
                @slot('type')
                    danger text-center
                @endslot
                @lang('admin/users/edit.blacklisted')
            @endcomponent
        @elseif($user->isWhitelisted())
            @component('components.alert')
                @slot('type')
                    success text-center
                @endslot
                    @lang('admin/users/edit.whitelisted')
            @endcomponent
        @endif
    </div>
    <div class="col-12 col-md-8 mx-auto d-flex">
        <div class="col-12 col-md-6">
            @include('components.errors')
            <form role="form" method="POST" action="{{ route('admin_users_update') }}">
                {{ csrf_field() }}
                <input name="_method" type="hidden" value="PATCH">
                <input name="id" type="hidden" value="{{ $user->id }}">
                <div class="form-group">
                    <label for="name" aria-label="Name">@lang('admin/users/edit.name'):</label>
                    <input type="text" class="form-control" id="name" name="name" aria-labelledby="name" tabindex="1" value="{{ $user->name }}" autofocus>
                </div>
                <div class="form-group">
                    <label for="email" aria-label="E-Mail">@lang('admin/users/edit.email'):</label>
                    <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="2" data-minlength="3" value="{{ $user->email }}">
                </div>
                <div class="form-group">
                    <label for="api_token" aria-label="API Key">@lang('admin/users/edit.api_key'):</label>
                    <input type="text" class="form-control" id="api_token" name="api_token" required aria-required="true" aria-labelledby="api_token" tabindex="3" data-minlength="3" value="{{ $user->api_token }}">
                </div>
                <div class="form-group">
                    <label for="requests_per_minute" aria-label="Requests per Minute">@lang('admin/users/edit.requests_per_minute'):</label>
                    <input type="number" class="form-control" id="requests_per_minute" name="requests_per_minute" required aria-required="true" aria-labelledby="requests_per_minute" tabindex="4" data-minlength="3" value="{{ $user->requests_per_minute }}">
                </div>
                <div class="form-group">
                    <label for="password" aria-label="Passwort">@lang('admin/users/edit.password'):</label>
                    <input type="password" class="form-control" id="password" name="password" aria-labelledby="password" data-minlength="8">
                </div>
                <div class="form-group">
                    <label for="notes">Notizen</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4" tabindex="5">{{ $user->notes }}</textarea>
                </div>
                <fieldset class="form-group">
                    <legend>@lang('admin/users/edit.options'):</legend>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="list" id="whitelisted" value="whitelisted" tabindex="6" {{ !$user->isWhitelisted()?:'checked' }}>
                            @lang('admin/users/edit.whitelist')
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="list" id="blacklisted" value="blacklisted" tabindex="7" {{ !$user->isBlacklisted()?:'checked' }}>
                            @lang('admin/users/edit.blacklist')
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="list" id="nooptions" value="nooptions" tabindex="8">
                            @lang('admin/users/edit.delete_options')
                        </label>
                    </div>
                </fieldset>
                <button type="submit" class="btn btn-warning my-3">@lang('admin/users/edit.edit')</button>
            </form>
        </div>
        <div class="col-12 col-md-6">
            <label>Stats:</label>
            @component('admin.components.card')
                @slot('content')
                    {{ \Carbon\Carbon::parse($user->last_login)->format('d.m.Y H:i') }}
                @endslot
                @slot('icon')
                    sign-in
                @endslot
                    @lang('admin/users/edit.last_login')
            @endcomponent

            @component('admin.components.card')
                @slot('content')
                    {{ count($user->apiRequests()->get()) }}
                @endslot
                @slot('icon')
                    random
                @endslot
                <a href="{{ route('admin_users_requests_list', $user->id) }}" class="text-muted">@lang('admin/users/edit.api_requests')</a>
            @endcomponent

            @component('admin.components.card')
                @slot('content')
                    {{ count($user->shortURLs()->get()) }}
                @endslot
                @slot('icon')
                    link
                @endslot
                <a href="{{ route('admin_users_urls_list', $user->id) }}" class="text-muted">ShortURLs</a>
            @endcomponent
            @unless(empty($user->notes))
            <div class="card">
                <div class="card-header bg-inverse text-white">
                    @lang('admin/users/edit.notes')
                </div>
                <div class="card-block">
                    <p class="card-text">{{ $user->notes }}</p>
                </div>
            </div>
            @endunless
        </div>
    </div>
@endsection

