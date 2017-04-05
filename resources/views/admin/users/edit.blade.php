@extends('layouts.admin')
@section('title', 'Edit User')

@section('content')
    <div class="col-12 col-md-4 mx-auto">
        @include('snippets.errors')
        <form role="form" method="POST" action="{{ route('admin_users_update') }}">
            {{ csrf_field() }}
            <input name="_method" type="hidden" value="PATCH">
            <input name="id" type="hidden" value="{{ $user->id }}">
            <div class="form-group">
                <label for="name" aria-label="Name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" aria-labelledby="name" tabindex="1" value="{{ $user->name }}" autofocus>
            </div>
            <div class="form-group">
                <label for="email" aria-label="E-Mail">E-Mail:</label>
                <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="2" data-minlength="3" value="{{ $user->email }}">
            </div>
            <div class="form-group">
                <label for="api_token" aria-label="API Key">API Key:</label>
                <input type="text" class="form-control" id="api_token" name="api_token" required aria-required="true" aria-labelledby="api_token" tabindex="3" data-minlength="3" value="{{ $user->api_token }}">
            </div>
            <div class="form-group">
                <label for="requests_per_minute" aria-label="Requests per Minute">Requests per Minute:</label>
                <input type="number" class="form-control" id="requests_per_minute" name="requests_per_minute" required aria-required="true" aria-labelledby="requests_per_minute" tabindex="4" data-minlength="3" value="{{ $user->requests_per_minute }}">
            </div>
            <div class="form-group">
                <label for="password" aria-label="Passwort">Passwort:</label>
                <input type="password" class="form-control" id="password" name="password" aria-labelledby="password" data-minlength="8">
            </div>
            <div class="form-group">
                <label for="notes">Notizen</label>
                <textarea class="form-control" id="notes" name="notes" rows="4" tabindex="5">{{ $user->notes }}</textarea>
            </div>
            <fieldset class="form-group">
                <legend>Optionen:</legend>
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="list" id="whitelisted" value="whitelisted" tabindex="6" {{ !$user->isWhitelisted()?:'checked' }}>
                        API Key von Throttling ausschließen
                    </label>
                </div>
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="list" id="blacklisted" value="blacklisted" tabindex="7" {{ !$user->isBlacklisted()?:'checked' }}>
                        API Key sperren
                    </label>
                </div>
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="list" id="nooptions" value="nooptions" tabindex="8">
                        Optionen löschen
                    </label>
                </div>
            </fieldset>
            <button type="submit" class="btn btn-warning my-3">Edit</button>
        </form>
    </div>
@endsection

