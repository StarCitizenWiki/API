@extends('layouts.app')
@section('title')
    @lang('auth/account/shorturls/edit.header')
@endsection

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                @include('components.errors')
                <form role="form" method="POST" action="{{ route('account_urls_update') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="PATCH">
                    <input name="id" type="hidden" value="{{ $url->id }}">
                    <div class="form-group">
                        <label for="url" aria-label="Name">@lang('auth/account/shorturls/edit.url'):</label>
                        <input type="url" class="form-control" id="url" name="url" aria-labelledby="url" tabindex="1" value="{{ $url->url }}" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="hash_name" aria-label="Name">@lang('auth/account/shorturls/edit.name'):</label>
                        <input type="text" class="form-control" id="hash_name" name="hash_name" required aria-required="true" aria-labelledby="hash_name" tabindex="2" data-minlength="3" value="{{ $url->hash_name }}">
                    </div>
                    <div class="form-group">
                        <label for="expires" aria-label="expires">@lang('auth/account/shorturls/edit.expires'):</label>
                        <input type="datetime-local" class="form-control" id="expires" name="expires" aria-required="true" aria-labelledby="expires" tabindex="3" value="@unless(is_null($url->expires)){{ \Carbon\Carbon::parse($url->expires)->format('Y-m-d\TH:i') }}@endunless">
                    </div>
                    <button type="submit" class="btn btn-warning my-3">@lang('auth/account/shorturls/edit.edit')</button>
                </form>
            </div>
        </div>
    </div>
@endsection

