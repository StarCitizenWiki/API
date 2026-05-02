@extends('layouts.app')

@php
    $pageTitleDecoded = html_entity_decode(html_entity_decode($pageTitle));
@endphp

@section('title')
    {!! $pageTitleDecoded !!}
@endsection
@section('meta_description', "{$pageTitle} details.")

@section('content')
    @php
        $imageId = data_get($image, 'id');
        $name = data_get($image, 'name');
        $alt = data_get($image, 'alt');
        $rsiUrl = data_get($image, 'rsi_url');
        $apiUrl = data_get($image, 'api_url');
        $size = data_get($image, 'size');
        $mimeType = data_get($image, 'mime_type');
        $lastModified = data_get($image, 'last_modified');
        $tags = data_get($image, 'tags', []);
        $commLinks = data_get($image, 'comm_links', []);
        $duplicates = data_get($image, 'duplicates', []);
        $baseImage = data_get($image, 'base_image');

        $commLinks = is_array($commLinks) ? $commLinks : [];
        $tags = is_array($tags) ? $tags : [];
        $duplicates = is_array($duplicates) ? $duplicates : [];

        $commLinksPreview = collect($commLinks)->take(5);
        $commLinksRemaining = collect($commLinks)->slice(5);
        $sizeMb = $size !== null ? round(((float) $size) / (1024 * 1024), 2) : null;
        $isImage = is_string($mimeType) && str_contains($mimeType, 'image');
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-subtle">
                <ul>
                    <li><a href="{{ route('web.comm-links.index') }}">Comm-Links</a></li>
                    <li><a href="{{ route('web.comm-links.images.index') }}">Images</a></li>
                    <li>{{ $name ?? $imageId }}</li>
                </ul>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight">Comm-Link Image</h1>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <figure class="px-6 pt-6">
                        <img
                            src="{{ $rsiUrl ?? '' }}"
                            alt="{{ $alt ?? 'Comm-Link image' }}"
                            class="rounded-box w-full object-cover"
                            loading="lazy"
                        >
                    </figure>
                    <div class="card-body gap-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold">{{ $name ?? 'Image' }}</div>
                                <div class="text-xs text-subtle">{{ $alt ?: 'No description available.' }}</div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if ($rsiUrl)
                                    <a class="btn btn-outline btn-sm" href="{{ $rsiUrl }}" target="_blank" rel="noreferrer">Source</a>
                                @endif
                                @if ($apiUrl)
                                    <a class="btn btn-outline btn-sm" href="{{ $apiUrl }}" target="_blank">API</a>
                                @endif
                                @auth
                                    @if ($isImage)
                                        <a class="btn btn-outline btn-sm" href="{{ route('web.comm-links.images.similar', $imageId) }}" target="_blank">Similar</a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Metadata</h2>
                        <x-dl-section dlClass="grid gap-4">
                            <x-dt-dd label="ID">{{ $imageId ?? '-' }}</x-dt-dd>
                            <x-dt-dd label="Name">{{ $name ?? '-' }}</x-dt-dd>
                            <x-dt-dd label="Mime Type">{{ $mimeType ?? '-' }}</x-dt-dd>
                            <x-dt-dd label="Size">
                                @if ($sizeMb !== null)
                                    {{ $sizeMb }} MB
                                @else
                                    -
                                @endif
                            </x-dt-dd>
                            <x-dt-dd label="Last Modified">{{ $lastModified ?? '-' }}</x-dt-dd>
                        </x-dl-section>
                    </div>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Duplicates</h2>
                        @if ($baseImage)
                            <div class="text-sm text-subtle">
                                Base image:
                                <a class="link link-primary" href="{{ route('web.comm-links.images.show', $baseImage['id']) }}">
                                    {{ $baseImage['name'] ?? $baseImage['id'] }}
                                </a>
                            </div>
                        @endif
                        @if ($duplicates !== [])
                            <div class="flex flex-col gap-2 text-sm">
                                @foreach ($duplicates as $duplicate)
                                    <a class="link link-primary" href="{{ route('web.comm-links.images.show', $duplicate['id']) }}">
                                        {{ $duplicate['name'] ?? $duplicate['id'] }}
                                    </a>
                                @endforeach
                            </div>
                        @elseif (! $baseImage)
                            <div class="text-sm text-subtle">No duplicates available.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body gap-4">
                <h2 class="card-title text-base">Comm-Links</h2>
                @if ($commLinks !== [])
                    <div class="flex flex-col gap-2 text-sm">
                        @foreach ($commLinksPreview as $commLink)
                            <a class="link link-primary" href="{{ route('web.comm-links.show', $commLink['id']) }}">
                                {{ $commLink['id'] ?? '-' }} - {{ $commLink['title'] ?? 'Comm-Link' }}
                            </a>
                        @endforeach
                    </div>
                    @if ($commLinksRemaining->isNotEmpty())
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                            <input type="checkbox" />
                            <div class="collapse-title text-sm font-semibold">
                                Show all {{ count($commLinks) }} Comm-Links
                            </div>
                            <div class="collapse-content">
                                <div class="flex flex-col gap-2 text-sm">
                                    @foreach ($commLinksRemaining as $commLink)
                                        <a class="link link-primary" href="{{ route('web.comm-links.show', $commLink['id']) }}">
                                            {{ $commLink['id'] ?? '-' }} - {{ $commLink['title'] ?? 'Comm-Link' }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-sm text-subtle">No comm-links available.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
