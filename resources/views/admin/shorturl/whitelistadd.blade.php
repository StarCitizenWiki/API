@extends('layouts.app')
@section('title', 'Add Whitelist URL')

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                @include('snippets.errors')
                <form role="form" method="POST" action="{{ route('admin_urls_whitelist_add') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="POST">
                    <div class="form-group">
                        <label for="url" aria-label="Name">URL:</label>
                        <input type="url" class="form-control" id="url" name="url" aria-labelledby="url" tabindex="1" autofocus>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="internal" name="internal[]" aria-labelledby="internal" tabindex="2" checked> Url öffentlich auflisten
                        </label>
                    </div>
                    <button type="submit" class="btn btn-success my-3">Add</button>
                </form>
            </div>
        </div>
    </div>
@endsection

