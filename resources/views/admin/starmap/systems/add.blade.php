@extends('layouts.app')
@section('title', 'Add Starmap System')

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                @include('snippets.errors')
                <form role="form" method="POST" action="{{ route('admin_starmap_systems_add') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="code" aria-label="Code">Code:</label>
                        <input type="text" class="form-control" id="code" name="code" aria-labelledby="code" tabindex="1" autofocus>
                    </div>

                    <button type="submit" class="btn btn-success my-3">Add</button>
                </form>
            </div>
        </div>
    </div>
@endsection

