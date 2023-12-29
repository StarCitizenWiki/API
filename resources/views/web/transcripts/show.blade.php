@extends('web.layouts.default_wide')

@section('title', __('Transcript').' - '.$transcript->title ?? '')

@section('head__content')
    @parent
    <style>
        .card-body p {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex mb-3">
        @unless(null === $prev)
            <a href="{{ route('web.transcripts.show', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriges')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriges')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.transcripts.show', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächstes')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled ml-auto">@lang('Nächstes')</a>
        @endunless
    </div>
    <div class="card">
        <div class="card-header">
            <h4>
                {{ $transcript->title ?? $transcript->source_title }}
            </h4>
        </div>
        <div class="card-body">
            @include('components.messages')
            <div class="row">
                <div class="col-12 col-xl-8">
                    <nav class="mb-3">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="nav-en_EN-tab" data-toggle="tab" href="#english" role="tab" aria-controls="english" aria-selected="true">
                                @lang('en_EN')
                            </a>

                            <a class="nav-item nav-link" id="nav-de_DE-tab" data-toggle="tab" href="#german" role="tab" aria-controls="german" aria-selected="false">
                                @lang('de_DE')
                            </a>

                            <a class="nav-item nav-link" id="nav-description-tab" data-toggle="tab" href="#description" role="tab" aria-controls="meta" aria-selected="false">
                                @lang('Beschreibung')
                            </a>

                            <a class="nav-item nav-link" href="{{ route('web.transcripts.edit', $transcript->getRouteKey()) }}" aria-selected="false">
                                @lang('Bearbeiten')
                            </a>

                            <a class="nav-item nav-link" href="{{ config('api.wiki_url') }}/Transkript:{{ $transcript->title }}?veaction=edit" aria-selected="false">
                                @lang('Bearbeiten (Wiki)') <em class="fa fa-external-link-alt fa-sm" data-fa-transform="up-2"></em>
                            </a>
                        </div>
                    </nav>

                    <div class="tab-content" id="nav-tab-translations">
                        <div class="tab-pane fade show active" id="english" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                            {!! empty($transcript->english()->translation) ? 'Nicht vorhanden' : nl2br($transcript->english()->translation) !!}
                        </div>

                        <div class="tab-pane fade show" id="german" role="tabpanel" aria-labelledby="nav-de_DE-tab">
                            {!! empty($transcript->german()->translation) ? 'Nicht vorhanden' : nl2br($transcript->german()->translation) !!}
                        </div>

                        <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="nav-description-tab">
                            {!! empty($transcript->youtube_description) ? 'Nicht vorhanden' : nl2br($transcript->youtube_description) !!}
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4 mt-xl-0 mt-5">
                    <table class="table mb-0">
                        <tr>
                            <th class="border-top-0" colspan="2"><img src="{{ $transcript->thumbnail }}" class="img-fluid" alt="thumb" /></th>
                        </tr>
                        <tr>
                            <th>@lang('Titel')</th>
                            <td>{{ $transcript->title }}</td>
                        </tr>
                        <tr>
                            <th>@lang('Playlist')</th>
                            <td>{{ $transcript->playlist_name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('YouTube')</th>
                            <td>{{ $transcript->youtube_url }}</td>
                        </tr>
                        <tr>
                            <th>@lang('Veröffentlichung')</th>
                            <td>{{ $transcript->upload_date->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <th>@lang('Dauer')</th>
                            <td>{{ gmdate('H:i:s', $transcript->runtime) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    <script>
        const updateNavHash = (hash) => {
            const links = document.querySelectorAll('.nav-bar a');
            links.forEach(link => {
                link.href = link.href.split('#')[0] + hash;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let url = location.href.replace(/\/$/, '');

            if (location.hash) {
                const hash = url.split('#');
                $('#nav-tab a[href="#' + hash[1] + '"]').tab('show');
                url = location.href.replace(/\/#/, '#');
                history.replaceState(null, null, url);
                updateNavHash('#'+hash[1]);
            }

            $('a[data-toggle="tab"]').on('click', function () {
                let newUrl;
                const hash = $(this).attr('href');
                newUrl = url.split('#')[0] + hash;
                updateNavHash(hash);

                history.replaceState(null, null, newUrl)
            })
        })
    </script>
@endsection