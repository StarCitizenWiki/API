@extends('layouts.admin')
@section('title')
    @lang('admin/starmap/systems/add.header')
@endsection

@section('content')
    <div class="col-12 col-md-4 mx-auto">
        @include('components.errors')
        <form role="form" method="POST" action="{{ route('admin_starmap_systems_add') }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="code" aria-label="Code">@lang('admin/starmap/systems/add.code'):</label>
                <input type="text" class="form-control" id="code" name="code" aria-labelledby="code" tabindex="1" autofocus>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="exclude" name="exclude" aria-labelledby="exclude" tabindex="2" value="1"> @lang('admin/starmap/systems/add.exclude')
                </label>
            </div>

            <button type="submit" class="btn btn-success my-3">@lang('admin/starmap/systems/add.add')</button>
        </form>
    </div>
@endsection