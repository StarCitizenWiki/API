@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Edit Account')
@section('lead', 'Edit Account')

@section('content')
    @include('layouts.heading');
    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-6 offset-md-3 mt-5">
                @include('snippets.errors')
                <form role="form" method="POST" action="{{ route('edit_account') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="PATCH">
                    <div class="form-group">
                        <label for="name" aria-label="Name">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" aria-labelledby="name" tabindex="1" value="{{ $user->name }}" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="email" aria-label="E-Mail">E-Mail:</label>
                        <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="2" data-minlength="3" value="{{ $user->email }}">
                    </div>
                    <div class="form-group">
                        <label for="password" aria-label="Passwort">Passwort:</label>
                        <input type="password" class="form-control" id="password" name="password" aria-labelledby="password" tabindex="3" data-minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" aria-label="Passwort">Passwort:</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" aria-labelledby="password" tabindex="4" data-minlength="8">
                    </div>
                    <button type="submit" class="btn btn-warning my-3">Edit</button>
                </form>
            </div>
        </div>
    </div>
@endsection

