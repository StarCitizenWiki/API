@extends('user.layouts.default_wide')

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
            <a href="{{ route('web.user.transcripts.show', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriges')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriges')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.user.transcripts.show', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächstes')</a>
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
            <nav class="mb-3">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-en_EN-tab" data-toggle="tab" href="#english" role="tab" aria-controls="english" aria-selected="true">
                        @lang('en_EN')
                    </a>

                    <a class="nav-item nav-link" id="nav-de_DE-tab" data-toggle="tab" href="#german" role="tab" aria-controls="german" aria-selected="false">
                        @lang('de_DE')
                    </a>

                    <a class="nav-item nav-link" id="nav-meta-tab" data-toggle="tab" href="#meta" role="tab" aria-controls="meta" aria-selected="false">
                        @lang('Metadaten')
                    </a>

                    <a class="nav-item nav-link" href="{{ route('web.user.transcripts.edit', $transcript->getRouteKey()) }}" aria-selected="false">
                        @lang('Bearbeiten')
                    </a>

                    <a class="nav-item nav-link" href="{{ config('api.wiki_url') }}/Transkript:{{ $transcript->wiki_id }}?veaction=edit" aria-selected="false">
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

                <div class="tab-pane fade" id="meta" role="tabpanel" aria-labelledby="nav-meta-tab">
                    <table class="table mb-0">
                        <tr>
                            <th class="border-top-0">Wiki ID</th>
                            <td class="border-top-0">{{ $transcript->wiki_id }}</td>
                        </tr>
                        <tr>
                            <th>Titel Quelle</th>
                            <td>{{ $transcript->source_title ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Quell Url</th>
                            <td>
                                <a href="{{ $transcript->source_url }}" target="_blank">
                                    {{ $transcript->source_url ?? 'Keine Quell URL vorhanden' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Veröffentlichung Quelle</th>
                            <td>{{ $transcript->source_published_at ? $transcript->source_published_at->format('d.m.Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Titel</th>
                            <td>{{ $transcript->tile ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>YouTube Url</th>
                            <td>
                                <a href="{{ $transcript->youtube_url }}" target="_blank">
                                    {{ $transcript->youtube_url ?? 'Keine YouTube URL vorhanden' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Veröffentlichung</th>
                            <td>{{ $transcript->published_at ? $transcript->published_at->format('d.m.Y') : $transcript->created_at->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <th>Format</th>
                            <td>{{ $transcript->format->name }}</td>
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
      $(document).ready(() => {
        let url = location.href.replace(/\/$/, '');

        if (location.hash) {
          const hash = url.split('#');
          $('#nav-tab a[href="#' + hash[1] + '"]').tab('show');
          url = location.href.replace(/\/#/, '#');
          history.replaceState(null, null, url)
        }

        $('a[data-toggle="tab"]').on('click', function () {
          let newUrl;
          const hash = $(this).attr('href');
          newUrl = url.split('#')[0] + hash;

          history.replaceState(null, null, newUrl)
        })
      })
    </script>
@endsection