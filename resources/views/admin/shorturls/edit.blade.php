@extends('layouts.admin')
@section('title')
    @lang('admin/shorturls/edit.header')
@endsection

@section('content')
    <div class="col-12 col-md-4 mx-auto">
        @include('components.errors')
        <form role="form" method="POST" action="{{ route('admin_urls_update') }}">
            {{ csrf_field() }}
            <input name="_method" type="hidden" value="PATCH">
            <input name="id" type="hidden" value="{{ $url->id }}">
            <div class="form-group">
                <label for="url" aria-label="Name">@lang('admin/shorturls/edit.url'):</label>
                <input type="url" class="form-control" id="url" name="url" aria-labelledby="url" tabindex="1" value="{{ $url->url }}" autofocus>
            </div>
            <div class="form-group">
                <label for="hash_name" aria-label="Name">@lang('admin/shorturls/edit.name'):</label>
                <input type="text" class="form-control" id="hash_name" name="hash_name" required aria-required="true" aria-labelledby="hash_name" tabindex="2" data-minlength="3" value="{{ $url->hash_name }}">
            </div>
            <div class="form-group">
                <label for="expires" aria-label="expires">@lang('admin/shorturls/edit.expired'):</label>
                <input type="datetime-local" class="form-control" id="expires" name="expires" aria-required="true" aria-labelledby="expires" tabindex="3" value="@unless(is_null($url->expires)){{ \Carbon\Carbon::parse($url->expires)->format('Y-m-d\TH:i') }}@endunless">
            </div>
            <div class="form-group">
                <label for="user_id">@lang('admin/shorturls/edit.owner'):</label>
                <select class="form-control" id="user_id" name="user_id">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @if($url->user_id == $user->id) {{ 'selected' }}@endif>[{{ $user->id }}] {{ $user->email }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-warning my-3">@lang('admin/shorturls/edit.edit')</button>
        </form>
    </div>
@endsection

