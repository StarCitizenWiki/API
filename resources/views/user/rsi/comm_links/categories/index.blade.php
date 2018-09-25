@extends('user.layouts.default')

@section('title', __('Comm Link Kategorien'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Comm Link Kategorien')</h4>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 col-lg-6 mx-auto">
                    @foreach($categories as $category)
                        <a class="btn btn-block btn-outline-secondary text-center" href="{{ route('web.user.rsi.comm-links.categories.show', $category->getRouteKey()) }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection