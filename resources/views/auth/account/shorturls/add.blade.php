@extends('layouts.app')
@section('title')
    @lang('auth/account/shorturls/add.header')
@endsection

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                @include('components.errors')
                <form role="form" method="POST" action="{{ route('account_urls_add') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="POST">
                    <div class="form-group">
                        <label for="url" aria-label="Name">@lang('auth/account/shorturls/add.url'):</label>
                        <input type="url" class="form-control" id="url" name="url" aria-labelledby="url" tabindex="1" autofocus value="{{ old('url') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="hash_name" aria-label="Name">@lang('auth/account/shorturls/add.name'):</label>
                        <input type="text" class="form-control" id="hash_name" name="hash_name" aria-required="true" aria-labelledby="hash_name" tabindex="2" data-minlength="3" value="{{ old('hash_name') }}">
                    </div>
                    <div class="form-group">
                        <label for="expires" aria-label="expires">@lang('auth/account/shorturls/add.expires'):</label>
                        <input type="datetime-local" class="form-control" id="expires" name="expires" aria-required="true" aria-labelledby="expires" tabindex="3" value="{{ old('expires') }}">
                    </div>
                    <button type="submit" class="btn btn-success my-3">@lang('auth/account/shorturls/add.add')</button>
                </form>
            </div>
        </div>
    </div>
@endsection
