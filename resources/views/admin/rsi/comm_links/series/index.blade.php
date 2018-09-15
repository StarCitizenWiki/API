@extends('admin.layouts.default')

@section('title', __('Comm Link Serien'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Comm Link Serien')</h4>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 col-lg-6 mx-auto">
                    @foreach($series as $serie)
                        <a class="btn btn-block btn-outline-secondary text-center" href="{{ route('web.admin.rsi.comm-links.series.show', $serie->getRouteKey()) }}">
                            {{ $serie->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection