@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Add URL')
@section('lead', 'Add URL')

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                @include('snippets.errors')
                <form role="form" method="POST" action="{{ route('account_urls_add') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="POST">
                    <input name="{{ AUTH_KEY_FIELD_NAME }}" type="hidden" value="{{ Auth::user()->api_token }}">
                    <div class="form-group">
                        <label for="url" aria-label="Name">URL:</label>
                        <input type="url" class="form-control" id="url" name="url" aria-labelledby="url" tabindex="1" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="hash_name" aria-label="Name">Name (Optional):</label>
                        <input type="text" class="form-control" id="hash_name" name="hash_name" aria-required="true" aria-labelledby="hash_name" tabindex="2" data-minlength="3">
                    </div>
                    <button type="submit" class="btn btn-success my-3">Add</button>
                </form>
            </div>
        </div>
    </div>
@endsection

