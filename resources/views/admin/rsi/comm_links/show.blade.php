@extends('admin.layouts.default_wide')

@section('title', __('Comm Link').' - '.$commLink->title)

@section('head__content')
    @parent
    <style>
        .card-body p {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>
                {{ $commLink->title }}
            </h4>
        </div>
        <div class="card-body">
            {!! preg_replace('/(?:\<br>\s?)+/', '<br>', $commLink->english()->translation) !!}
            <hr>
            <h5>Links in diesem Comm Link: ({{ count($commLink->links) }})</h5>
            @forelse($commLink->links as $link)
                <span class="d-block"><a href="{{ $link->href }}" target="_blank">{{ $link->text }}</a> &mdash; {{ $link->href }}</span>
            @empty
                Keine Links vorhanden
            @endforelse
            <hr>
            <h5>Bilder in diesem Comm Link: ({{ count($commLink->images) }})</h5>
            @forelse($commLink->images as $image)
                <a class="" href="{{ $image->src }}" target="_blank"><img src="{{ str_replace('source', 'post', $image->src) }}" class="img-thumbnail" style="max-width: 150px;"></a>
            @empty
                Keine Bilder vorhanden
            @endforelse
            <hr>
            <h5>Metadaten:</h5>
            <table class="table mb-0">
                <tr>
                    <th>ID</th>
                    <td>{{ $commLink->cig_id }}</td>
                </tr>
                <tr>
                    <th>Veröffentlichung</th>
                    <td>{{ $commLink->created_at->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <th>Kategorie</th>
                    <td>
                        <a href="{{ route('web.admin.rsi.comm_links.categories.show', $commLink->category->getRouteKey()) }}">
                            {{ $commLink->category->name }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Channel</th>
                    <td>
                        <a href="{{ route('web.admin.rsi.comm_links.channels.show', $commLink->channel->getRouteKey()) }}">
                            {{ $commLink->channel->name }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Serie</th>
                    <td>
                        <a href="{{ route('web.admin.rsi.comm_links.series.show', $commLink->series->getRouteKey()) }}">
                            {{ $commLink->series->name }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Url</th>
                    <td>
                        <a href="{{ $commLink->url ?? "https://robertsspaceindustries.com/comm-link/SCW/{$commLink->cig_id}-API" }}" target="_blank">
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
    </div>
@endsection
