@extends('user.layouts.default_wide')

@section('title', __('Comm Link Bilder'))

@section('content')
    <h3>Comm Link Bilder</h3>
    <div class="card-columns" style="column-count: 5">
        @foreach($images as $image)
            <div class="card">
                @if($image->local)
                    <a href="{{ asset("storage/comm_link_images/{$image->dir}/{$image->name}") }}" target="_blank" class="text-center d-block">
                @else
                    <a href="{{ $image->url }}" target="_blank" class="text-center d-block">
                @endif
                    <img src="{{ config('api.rsi_url') }}{{ str_replace('source', 'post', $image->src) }}" alt="{{ $image->alt ?? __('Kein alt Text verfügbar') }}" class="card-img-top">
                </a>
                @unless(empty($image->alt))
                <div class="card-body">
                    Bildbeschreibung: {{ $image->alt }}
                </div>
                @endunless
                <ul class="list-group list-group-flush collapse" id="comm_link_container_{{ $loop->index }}">
                    @foreach($image->commLinks as $commLink)
                        <li class="list-group-item">
                            <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}" class="card-link">{{ $commLink->cig_id }} &mdash; {{ $commLink->title }}</a>
                        </li>
                    @endforeach
                </ul>
                <div class="card-footer">
                    <a data-toggle="collapse" href="#comm_link_container_{{ $loop->index }}" role="button" aria-expanded="false" aria-controls="comm_link_container_{{ $loop->index }}">
                        @lang('Comm Links ausklappen')
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <div>
        {{ $images->links() }}
    </div>
@endsection