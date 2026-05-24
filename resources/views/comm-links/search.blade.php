@extends('layouts.app')

@section('title', 'Comm-Link Search')
@section('meta_description', 'Search comm-links by title, content, media URL, media name, or reverse image.')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-subtle">
                <ul>
                    <li><a href="{{ route('web.comm-links.index') }}">Comm-Links</a></li>
                    <li>Search</li>
                </ul>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold tracking-tight" data-testid="comm-links-search-heading">Comm-Link Search</h1>
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

        <section class="flex flex-col gap-4" aria-labelledby="text-search-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="text-search-heading" class="text-lg font-semibold tracking-tight" data-testid="comm-links-search-text-heading">Text Search</h2>
                <span class="badge badge-secondary badge-outline">Comm-Link records</span>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <form method="GET" action="{{ route('web.comm-links.index') }}" class="card-body gap-4" data-testid="comm-links-search-title-form">
                        <input type="hidden" name="search" value="title" data-testid="comm-links-search-title-mode">

                        <div class="flex flex-col gap-1">
                            <h3 class="card-title">Title</h3>
                            <p class="text-sm text-subtle">Search by Comm-Link title or exact CIG ID.</p>
                        </div>

                        <label class="input w-full">
                            <span class="label">Title or CIG ID</span>
                            <input
                                type="search"
                                name="query"
                                value="{{ old('query') }}"
                                placeholder="Banu Merchantman"
                                autocomplete="off"
                                required
                            />
                        </label>

                        <button type="submit" class="btn btn-primary">Search title</button>
                    </form>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <form method="GET" action="{{ route('web.comm-links.index') }}" class="card-body gap-4" data-testid="comm-links-search-content-form">
                        <input type="hidden" name="search" value="content" data-testid="comm-links-search-content-mode">

                        <div class="flex flex-col gap-1">
                            <h3 class="card-title">Content</h3>
                            <p id="content-search-help" class="text-sm text-subtle">Search full Comm-Link article content.</p>
                        </div>

                        <label class="input w-full">
                            <span class="label">Content query</span>
                            <input
                                type="search"
                                name="query"
                                value="{{ old('query') }}"
                                placeholder="quantum jump drive"
                                aria-describedby="content-search-help"
                                autocomplete="off"
                                required
                            />
                        </label>

                        <button type="submit" class="btn btn-primary">Search content</button>
                    </form>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm lg:col-span-2">
                    <form method="GET" action="{{ route('web.comm-links.index') }}" class="card-body gap-4" data-testid="comm-links-search-media-url-form">
                        <input type="hidden" name="search" value="media-url" data-testid="comm-links-search-media-url-mode">

                        <div class="flex flex-col gap-1">
                            <h3 class="card-title">Media URL</h3>
                            <p id="media-url-help" class="text-sm text-subtle">Find comm-links that reference a specific RSI-hosted image URL.</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-start">
                            <div class="flex flex-col gap-1">
                                <label class="input w-full">
                                    <span class="label">Image URL</span>
                                    <input
                                        type="url"
                                        name="url"
                                        value="{{ old('url') }}"
                                        placeholder="https://robertsspaceindustries.com/media/..."
                                        pattern="https?:\/\/(?:media\.)?robertsspaceindustries.com\/.*"
                                        aria-describedby="media-url-help"
                                        required
                                    />
                                </label>
                                <span class="label-text-alt text-subtle">
                                    Use https://robertsspaceindustries.com/media/... or https://media.robertsspaceindustries.com/...
                                </span>
                            </div>
                            <button type="submit" class="btn btn-primary">Search media URL</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4" aria-labelledby="image-search-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="image-search-heading" class="text-lg font-semibold tracking-tight" data-testid="comm-links-search-image-heading">Image Search</h2>
                <span class="badge badge-outline">Comm-Link images</span>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <form method="GET" action="{{ route('web.comm-links.images.search') }}" class="card-body gap-4" data-testid="comm-links-search-media-name-form">
                        <div class="flex flex-col gap-1">
                            <h3 class="card-title">Media Name</h3>
                            <p class="text-sm text-subtle">Search Comm-Link images by filename.</p>
                        </div>

                        <label class="input w-full">
                            <span class="label">Filename contains</span>
                            <input
                                type="search"
                                name="query"
                                value="{{ old('query') }}"
                                placeholder="Carrack"
                                autocomplete="off"
                                required
                            />
                        </label>

                        <button type="submit" class="btn btn-primary">Search media name</button>
                    </form>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <form
                        method="POST"
                        action="{{ route('web.comm-links.images.reverse-search') }}"
                        enctype="multipart/form-data"
                        class="card-body gap-4"
                        data-testid="comm-links-search-reverse-image-form"
                    >
                        @csrf

                        <div class="flex flex-col gap-1">
                            <h3 class="card-title">Reverse Image Search</h3>
                            <p class="text-sm text-subtle">Upload an image to find similar Comm-Link visuals.</p>
                        </div>

                        <div class="flex flex-col gap-1 w-full">
                            <span class="label">Image file</span>
                            <input type="file" name="image" accept="image/*" class="file-input w-full" data-testid="comm-links-search-image-input" required />
                            <span class="label-text-alt text-subtle">Max file size 5 MB.</span>
                        </div>

                        <div class="flex flex-col gap-1 w-full">
                            <span class="label">Similarity threshold</span>
                            <select name="similarity" class="select w-full">
                                <option value="">Default (75%)</option>
                                <option value="95">95% (Exact)</option>
                                <option value="80">80% (Very similar)</option>
                                <option value="60">60% (Similar)</option>
                                <option value="50">50% (Loose)</option>
                                <option value="25">25% (Very loose)</option>
                            </select>
                            <span class="label-text-alt text-subtle">Lower values return more results.</span>
                        </div>

                        <button type="submit" class="btn btn-primary">Search by image</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
