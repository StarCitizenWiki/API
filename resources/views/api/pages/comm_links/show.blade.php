@extends('api.layouts.default')

@section('title', __('Comm-Link').' - '.$commLink->title)

@section('head__content')
    @parent
    <style>
        .card-body p {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex mb-3 nav-bar">
        @unless(null === $prev)
            <a href="{{ route('web.api.comm-links.show', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriger')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriger')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.api.comm-links.show', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächster')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled ml-auto">@lang('Nächster')</a>
        @endunless
    </div>
    <div class="card">
        <div class="card-header">
            <h4>
                {{ $commLink->title }}
            </h4>
        </div>
        <div class="card-body">
            @include('components.messages')
            <nav class="mb-3">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-en_EN-tab" data-toggle="tab" href="#english" role="tab" aria-controls="english" aria-selected="true">
                        @lang('en_EN')
                    </a>

                    @unless(empty($commLink->english()->translation))
                    <a class="nav-item nav-link" id="nav-de_DE-tab" href="{{ config('api.wiki_url') }}/Comm-Link:{{ $commLink->cig_id }}" aria-selected="false" target="_blank">
                        @lang('de_DE') <em class="fal fa-external-link fa-sm" data-fa-transform="up-2"></em>
                    </a>
                    @endunless

                    <a class="nav-item nav-link" id="nav-api-tab" href="{{ route('web.user.rsi.comm-links.show', $commLink->cig_id) }}" aria-selected="false" target="_blank">
                        @lang('Intern') <em class="fal fa-external-link fa-sm" data-fa-transform="up-2"></em>
                    </a>

                    <a class="nav-item nav-link" id="nav-links-tab" data-toggle="tab" href="#links" role="tab" aria-controls="links" aria-selected="false">
                        @lang('Links') <span class="badge badge-primary">{{ count($commLink->links) }}</span>
                    </a>

                    <a class="nav-item nav-link" id="nav-images-tab" data-toggle="tab" href="#images" role="tab" aria-controls="images" aria-selected="false">
                        @lang('Bilder') <span class="badge badge-primary">{{ count($commLink->images) }}</span>
                    </a>

                    <a class="nav-item nav-link" id="nav-meta-tab" data-toggle="tab" href="#meta" role="tab" aria-controls="meta" aria-selected="false">
                        @lang('Metadaten')
                    </a>

                    <a class="nav-item nav-link" id="nav-api-tab" href="{{ app('api.url')->version('v1')->route('api.v1.rsi.comm-links.show', $commLink->cig_id) }}" aria-selected="false" target="_blank">
                        @lang('API') <em class="fal fa-external-link fa-sm" data-fa-transform="up-2"></em>
                    </a>
                </div>
            </nav>

            <div class="tab-content" id="nav-tab-translations">
                <div class="tab-pane fade show active" id="english" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                    {!! empty($commLink->english()->translation) ? 'Nicht vorhanden' : nl2br($commLink->english()->translation) !!}
                </div>

                <div class="tab-pane fade" id="links" role="tabpanel" aria-labelledby="nav-links-tab">
                    <ul>
                        @forelse($commLink->links as $link)
                            <li><a href="{{ $link->href }}" target="_blank">{{ $link->text }}</a> &mdash; {{ $link->href }}</li>
                        @empty
                            <li>Keine Links vorhanden</li>
                        @endforelse
                    </ul>
                </div>

                <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="nav-images-tab">
                    @forelse($commLink->images as $image)
                        <a class="" href="{{ $image->getLocalOrRemoteUrl() }}" target="_blank"><img src="{{ str_replace('source', 'post', $image->url) }}" alt="Comm-Link Image" class="img-thumbnail" style="max-width: 150px;"></a>
                    @empty
                        Keine Bilder vorhanden
                    @endforelse
                </div>

                <div class="tab-pane fade" id="meta" role="tabpanel" aria-labelledby="nav-meta-tab">
                    @forelse($commLink->images as $image)
                        <a class="" href="{{ $image->getLocalOrRemoteUrl() }}" target="_blank"><img src="{{ str_replace('source', 'post', $image->url) }}" alt="Comm-Link Image" class="img-thumbnail" style="max-width: 150px;"></a>
                    @empty
                        Keine Bilder vorhanden
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
