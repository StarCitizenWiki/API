@extends('user.layouts.default')

@section('title', __('Comm Link Channel'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Comm Link Channel')</h4>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 col-lg-6 mx-auto">
                    @foreach($channels as $channel)
                        <a class="btn btn-block btn-outline-secondary text-center" href="{{ route('web.user.rsi.comm-links.channels.show', $channel->getRouteKey()) }}">
                            {{ $channel->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection