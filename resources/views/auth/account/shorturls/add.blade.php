@extends('layouts.app')
@section('title', 'Add URL')

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                @include('components.errors')
                <form role="form" method="POST" action="{{ route('account_urls_add') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="POST">
                    <input name="{{ AUTH_KEY_FIELD_NAME }}" type="hidden" value="{{ Auth::user()->api_token }}">
                    <div class="form-group">
                        <label for="url" aria-label="Name">URL:</label>
                        <input type="url" class="form-control" id="url" name="url" aria-labelledby="url" tabindex="1" autofocus value="{{ old('url') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="hash_name" aria-label="Name">Name (Optional):</label>
                        <input type="text" class="form-control" id="hash_name" name="hash_name" aria-required="true" aria-labelledby="hash_name" tabindex="2" data-minlength="3" value="{{ old('hash_name') }}">
                    </div>
                    <div class="form-group">
                        <label for="expires" aria-label="expires">Expires (Optional):</label>
                        <input type="datetime-local" class="form-control" id="expires" name="expires" aria-required="true" aria-labelledby="expires" tabindex="3" value="{{ old('expires') }}">
                    </div>
                    <button type="submit" class="btn btn-success my-3">Add</button>
                </form>
            </div>
        </div>
    </div>
@endsection
