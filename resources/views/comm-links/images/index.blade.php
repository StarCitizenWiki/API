@extends('layouts.app')

@section('title', 'Comm-Link Images')
@section('meta_description', 'Comm-link images with reverse search, similarity matching, and metadata.')

@section('content')
    @php
        $images = is_array($images ?? null) ? $images : [];
        $searchType = $searchType ?? null;
        $searchQuery = $searchQuery ?? null;
        $currentPage = (int) data_get($pagination ?? [], 'current_page', 1);
        $lastPage = (int) data_get($pagination ?? [], 'last_page', 1);
        $total = data_get($pagination ?? [], 'total');
        $prevPage = $currentPage > 1 ? $currentPage - 1 : null;
        $nextPage = $currentPage < $lastPage ? $currentPage + 1 : null;

        $prevUrl = $prevPage ? request()->fullUrlWithQuery(['page' => null, 'page[number]' => $prevPage]) : null;
        $nextUrl = $nextPage ? request()->fullUrlWithQuery(['page' => null, 'page[number]' => $nextPage]) : null;
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-subtle">
                <ul>
                    <li><a href="{{ route('web.comm-links.index') }}">Comm-Links</a></li>
                    <li>Images</li>
                </ul>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold tracking-tight" data-testid="comm-links-images-heading">Comm-Link Images</h1>
                <div class="text-sm text-subtle">
                    Page {{ $currentPage }} of {{ max($lastPage, 1) }}
                    @if ($total !== null)
                        · {{ $total }} total
                    @endif
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-error text-sm">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($searchType)
            <div class="alert alert-info text-sm" data-testid="comm-links-images-search-summary">
                @if ($searchType === 'media-name')
                    Showing results for media name search: <span class="font-semibold">{{ $searchQuery ?: '-' }}</span>.
                @elseif ($searchType === 'reverse-image')
                    Showing reverse image matches.
                @endif
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-subtle">
                Ordered by latest upload date.
            </div>
            <div class="join">
                <a class="btn btn-outline btn-sm join-item @if (! $prevUrl) btn-disabled @endif" href="{{ $prevUrl ?? '#' }}">
                    Previous
                </a>
                <a class="btn btn-outline btn-sm join-item @if (! $nextUrl) btn-disabled @endif" href="{{ $nextUrl ?? '#' }}">
                    Next
                </a>
            </div>
        </div>

        @if ($images !== [])
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5" data-testid="comm-links-images-grid">
                @foreach ($images as $image)
                    <x-comm-links.image-card :image="$image" />
                @endforeach
            </div>
        @else
            <div class="text-sm text-subtle" data-testid="comm-links-images-empty-state">No images available.</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-subtle">
                Page {{ $currentPage }} of {{ max($lastPage, 1) }}
            </div>
            <div class="join">
                <a class="btn btn-outline btn-sm join-item @if (! $prevUrl) btn-disabled @endif" href="{{ $prevUrl ?? '#' }}">
                    Previous
                </a>
                <a class="btn btn-outline btn-sm join-item @if (! $nextUrl) btn-disabled @endif" href="{{ $nextUrl ?? '#' }}">
                    Next
                </a>
            </div>
        </div>
    </div>
@endsection
