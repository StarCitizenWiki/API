@extends('layouts.admin')
@section('title')
    @lang('admin/shorturls/whitelists/add.header')
@endsection

@section('content')
    <div class="col-12 col-md-4 mx-auto">
        @include('components.errors')
        <form role="form" method="POST" action="{{ route('admin_urls_whitelist_add') }}">
            {{ csrf_field() }}
            <input name="_method" type="hidden" value="POST">
            <div class="form-group">
                <label for="url" aria-label="Name">@lang('admin/shorturls/whitelists/add.url'):</label>
                <input type="url" class="form-control" id="url" name="url" aria-labelledby="url" tabindex="1" autofocus>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="internal" name="internal[]" aria-labelledby="internal" tabindex="2" checked> @lang('admin/shorturls/whitelists/add.show_in_public')
                </label>
            </div>
            <button type="submit" class="btn btn-success my-3">
                @lang('admin/shorturls/whitelists/add.add')
            </button>
        </form>
    </div>
@endsection

