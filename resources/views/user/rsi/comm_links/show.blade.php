@extends('user.layouts.default_wide')

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
    <div class="d-flex mb-3">
        @unless(null === $prev)
            <a href="{{ route('web.user.rsi.comm-links.show', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriger')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriger')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.user.rsi.comm-links.show', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächste')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled ml-auto">@lang('Nächste')</a>
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

                    <a class="nav-item nav-link" id="nav-de_DE-tab" href="{{ config('api.wiki_url') }}/Comm-Link:{{ $commLink->cig_id }}" aria-selected="false" target="_blank">
                        @lang('de_DE') <i class="fal fa-external-link fa-sm" data-fa-transform="up-2"></i>
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

                    <a class="nav-item nav-link" id="nav-changelog-tab" data-toggle="tab" href="#changelog" role="tab" aria-controls="changelog" aria-selected="false">
                        @lang('Verlauf')
                    </a>

                    <a class="nav-item nav-link" href="{{ config('api.wiki_url') }}/Comm-Link:{{ $commLink->cig_id }}?veaction=edit" aria-selected="false">
                        @lang('Bearbeiten') <i class="fal fa-external-link fa-sm" data-fa-transform="up-2"></i>
                    </a>

                    @can('web.user.rsi.comm-links.update')
                    <a class="nav-item nav-link" aria-selected="false" href="{{ route('web.user.rsi.comm-links.edit', $commLink->getRouteKey()) }}">
                        @lang('Einstellungen')
                    </a>
                    <a class="nav-item nav-link" id="nav-deepl-tab" aria-selected="false" data-toggle="tab" href="#deepl" role="tab" aria-controls="deepl">
                        @lang('DeepL Übersetzung')
                    </a>
                    @endcan
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
                        <a class="" href="{{ $image->getLocalOrRemoteUrl() }}" target="_blank"><img src="{{ str_replace('source', 'post', $image->url) }}" class="img-thumbnail" style="max-width: 150px;"></a>
                    @empty
                        Keine Bilder vorhanden
                    @endforelse
                </div>

                <div class="tab-pane fade" id="meta" role="tabpanel" aria-labelledby="nav-meta-tab">
                    <table class="table mb-0">
                        <tr>
                            <th class="border-top-0">ID</th>
                            <td class="border-top-0">{{ $commLink->cig_id }}</td>
                        </tr>
                        <tr>
                            <th>Veröffentlichung</th>
                            <td>{{ $commLink->created_at->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <th>Kategorie</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.categories.show', $commLink->category->getRouteKey()) }}">
                                    {{ $commLink->category->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Channel</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.channels.show', $commLink->channel->getRouteKey()) }}">
                                    {{ $commLink->channel->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Serie</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.series.show', $commLink->series->getRouteKey()) }}">
                                    {{ $commLink->series->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Url</th>
                            <td>
                                <a href="https://robertsspaceindustries.com{{ $commLink->url ?? "/comm-link/SCW/{$commLink->cig_id}-API" }}" target="_blank">
                                    {{ $commLink->url ?? 'Keine Original URL vorhanden' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Kommentare</th>
                            <td>{{ $commLink->comment_count }}</td>
                        </tr>
                    </table>
                </div>

                <div class="tab-pane fade" id="changelog" role="tabpanel" aria-labelledby="nav-changelog-tab">
                    @component('user.components.changelog_list', [
                        'changelogs' => $changelogs,
                    ])
                        Comm-Link
                    @endcomponent
                </div>

                @can('web.user.rsi.comm-links.update')
                    <div class="tab-pane fade" id="deepl" role="tabpanel" aria-labelledby="nav-deepl-tab">
                        {!! empty($commLink->german()->translation) ? 'Nicht vorhanden' : nl2br($commLink->german()->translation) !!}
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    <script>
      $(document).ready(() => {
        let url = location.href.replace(/\/$/, '')

        if (location.hash) {
          const hash = url.split('#')
          $('#nav-tab a[href="#' + hash[1] + '"]').tab('show')
          url = location.href.replace(/\/#/, '#')
          history.replaceState(null, null, url)
        }

        $('a[data-toggle="tab"]').on('click', function () {
          let newUrl
          const hash = $(this).attr('href')
          newUrl = url.split('#')[0] + hash

          history.replaceState(null, null, newUrl)
        })
      })
    </script>
@endsection