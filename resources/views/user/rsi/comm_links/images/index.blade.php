@extends('user.layouts.default_wide')

@section('title', __('Comm Link Bilder'))

@section('content')
    <h3>Comm Link Bilder</h3>
    <div class="card-columns">
        @foreach($images as $image)
            <div class="card">
                @if($image->local)
                    <a href="{{ asset("storage/comm_link_images/{$image->dir}/{$image->name}") }}" target="_blank" class="text-center d-block">
                @else
                    <a href="{{ $image->url }}" target="_blank" class="text-center d-block">
                @endif
                    <img src="{{ str_replace('source', 'post', $image->src) }}" alt="{{ $image->alt ?? __('Kein alt Text verfügbar') }}">
                </a>
                <ul class="list-group list-group-flush">
                    @foreach($image->commLinks as $commLink)
                        <li class="list-group-item">
                            <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}" class="card-link">{{ $commLink->cig_id }} &mdash; {{ $commLink->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
    <div>
        {{ $images->links() }}
    </div>
@endsection