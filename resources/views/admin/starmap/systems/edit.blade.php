@extends('layouts.admin')
@section('title')
    @lang('admin/starmap/systems/edit.header')
@endsection

@section('content')
    <div class="col-12 col-md-4 mx-auto">
        @include('components.errors')
        <form role="form" method="POST" action="{{ route('admin_starmap_systems_update') }}">
            {{ csrf_field() }}
            <input name="_method" type="hidden" value="PATCH">
            <input name="id" type="hidden" value="{{ $system->id }}">
            <div class="form-group">
                <label for="code" aria-label="Code">@lang('admin/starmap/systems/edit.code'):</label>
                <input type="text" class="form-control" id="code" name="code" aria-labelledby="code" tabindex="1" value="{{ $system->code }}" autofocus>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="exclude" name="exclude" aria-labelledby="exclude" tabindex="2" @if($system->isExcluded()){{ 'checked' }}@endif> @lang('admin/starmap/systems/edit.exclude')
                </label>
            </div>

            <button type="submit" class="btn btn-warning my-3">@lang('admin/starmap/systems/edit.edit')</button>
            <button onclick="event.preventDefault();
                    document.getElementById('delete-form').submit();" type="submit" class="btn btn-danger my-3">@lang('admin/starmap/systems/edit.delete')</button>
        </form>
        <form role="form" method="POST" id="delete-form" action="{{ route('admin_starmap_systems_delete') }}">
            {{ csrf_field() }}
            <input name="_method" type="hidden" value="DELETE">
            <input name="id" type="hidden" value="{{ $system->id }}">
        </form>
    </div>
    @unless(empty($content))
        <div class="col-12">
            <div class="card mt-5">
                <div class="card-header bg-inverse text-white">
                    <a class="text-white d-block" data-toggle="collapse" href="#content">@lang('admin/starmap/systems/edit.data')</a>
                </div>
                <div class="card-block collapse" id="content">
                    <pre class="card-text">{{ json_encode(json_decode($content), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    @endunless
@endsection

