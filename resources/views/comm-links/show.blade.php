@php use App\Models\System\Language; @endphp
@extends('layouts.app')

@php
    $pageTitleDecoded = html_entity_decode(html_entity_decode($pageTitle));
@endphp

@section('title')
    {!! $pageTitleDecoded !!}
@endsection
@section('meta_description', "{$pageTitle} Comm-Link details.")

@section('content')
    @php
        $title = data_get($commLink, 'title', 'Comm-Link');
        $commLinkId = data_get($commLink, 'id');
        $rsiUrl = data_get($commLink, 'rsi_url');
        $apiUrl = data_get($commLink, 'api_url');
        $channel = data_get($commLink, 'channel');
        $category = data_get($commLink, 'category');
        $series = data_get($commLink, 'series');
        $commentCount = data_get($commLink, 'comment_count');
        $createdAtHuman = data_get($commLink, 'created_at_human');
        $createdAt = data_get($commLink, 'created_at');

        $links = data_get($commLink, 'links', []);
        $images = data_get($commLink, 'images', []);
        $translations = data_get($commLink, 'translations');
        if (is_string($translations) && $translations !== '') {
            $translations = ['en' => $translations];
        }
        if (! is_array($translations)) {
            $translations = [];
        }

        $prevId = data_get($commLinkMeta, 'prev_id');
        $nextId = data_get($commLinkMeta, 'next_id');
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-subtle">
                <ul>
                    <li><a href="{{ route('web.comm-links.index') }}">Comm-Links</a></li>
                    <li>{{ $title }}</li>
                </ul>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight" data-testid="comm-link-show-heading">{{ $title }}</h1>
                @if ($channel)
                    <span class="badge badge-outline">{{ $channel }}</span>
                @endif
                @if ($category)
                    <span class="badge badge-neutral">{{ $category }}</span>
                @endif
                @if ($series)
                    <span class="badge badge-outline">{{ $series }}</span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            @if (is_int($prevId) && $prevId > 0)
                <a class="btn btn-outline btn-sm" data-testid="comm-link-prev-link"
                   href="{{ route('web.comm-links.show', $prevId) }}">Previous</a>
            @else
                <button class="btn btn-outline btn-sm" data-testid="comm-link-prev-button" disabled>Previous</button>
            @endif
            @if (is_int($nextId) && $nextId > 0)
                <a class="btn btn-outline btn-sm" data-testid="comm-link-next-link"
                   href="{{ route('web.comm-links.show', $nextId) }}">Next</a>
            @else
                <button class="btn btn-outline btn-sm" data-testid="comm-link-next-button" disabled>Next</button>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="flex flex-col gap-6 lg:col-span-2">
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Content</h2>
                        @if ($translations !== [])
                            <div class="space-y-3">
                                @foreach ($translations as $locale => $translation)
                                    @php
                                        $label = is_string($locale) ? Language::LABEL_MAP[$locale] : 'Translation '.$loop->iteration;
                                        $translationText = is_string($translation)
                                            ? $translation
                                            : json_encode($translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                    @endphp
                                    <div {{ $label !== 'English' ? 'data-remove' : '' }} class="collapse collapse-arrow border border-base-200 bg-base-100">
                                        <input type="checkbox"/>
                                        <div class="collapse-title text-sm font-semibold">{{ $label }}</div>
                                        <div class="collapse-content">
                                            @if ($translationText)
                                                <div
                                                    class="text-sm leading-relaxed text-emphasis whitespace-pre-line">
                                                    {!! nl2br(e($translationText)) !!}
                                                </div>
                                            @else
                                                <div class="text-sm text-subtle">No content available.</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-subtle">No translations available.</div>
                        @endif
                    </div>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Links</h2>
                        @if (is_array($links) && $links !== [])
                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>URL</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($links as $link)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $link['text'] ?? '-' }}</td>
                                            <td class="text-sm">
                                                @if (! empty($link['href']))
                                                    <a class="link link-primary" href="{{ $link['href'] }}"
                                                       target="_blank" rel="noreferrer">
                                                        {{ $link['href'] }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-subtle">No links available.</div>
                        @endif
                    </div>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="card-title text-base">Images</h2>
                            <span class="badge badge-outline">{{ is_array($images) ? count($images) : 0 }}</span>
                        </div>
                        @if (is_array($images) && $images !== [])
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($images as $image)
                                    <x-comm-links.image-card :image="$image"/>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-subtle">No images available.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Metadata</h2>
                        <x-dl-section dlClass="grid gap-4">
                            <x-dt-dd label="CIG ID">{{ $commLinkId ?? '-' }}</x-dt-dd>
                            <x-dt-dd label="Channel">{{ $channel ?? '-' }}</x-dt-dd>
                            <x-dt-dd label="Category">{{ $category ?? '-' }}</x-dt-dd>
                            <x-dt-dd label="Series">{{ $series ?? '-' }}</x-dt-dd>
                            <x-dt-dd label="Comments">{{ $commentCount ?? 0 }}</x-dt-dd>
                            <x-dt-dd label="Published">
                                {{ $createdAtHuman ?? '-' }}
                                @if ($createdAt)
                                    <span class="text-xs text-subtle">({{ $createdAt }})</span>
                                @endif
                            </x-dt-dd>
                        </x-dl-section>
                        <div class="flex flex-wrap gap-2">
                            @if ($rsiUrl)
                                <a class="btn btn-outline btn-sm" href="{{ $rsiUrl }}" target="_blank" rel="noreferrer">RSI
                                    Article</a>
                            @endif
                            @if ($apiUrl)
                                <a class="btn btn-outline btn-sm" href="{{ $apiUrl }}" target="_blank" rel="noreferrer">API</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
