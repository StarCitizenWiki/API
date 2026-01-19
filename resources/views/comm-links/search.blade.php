@extends('layouts.app')

@section('title', 'Comm-Link Search')
@section('meta_description', 'Search comm-links by title, media URL, media name, or reverse image.')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-base-content/70">
                <ul>
                    <li><a href="{{ route('web.comm-links.index') }}">Comm-Links</a></li>
                    <li>Search</li>
                </ul>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold tracking-tight">Comm-Link Search</h1>
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

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="card card-border bg-base-100 shadow">
                <form method="GET" action="{{ route('web.comm-links.index') }}" class="card-body gap-4">
                    <input type="hidden" name="search" value="title">

                    <div class="flex flex-col gap-1">
                        <h2 class="card-title">Title</h2>
                        <p class="text-sm text-base-content/70">Search by comm-link title or exact CIG ID.</p>
                    </div>

                    <label class="input w-full">
                        <span class="label">Title or CIG ID</span>
                        <input
                            type="text"
                            name="query"
                            value="{{ old('query') }}"
                            placeholder="Banu Merchantman"
                            required
                        />
                    </label>

                    <button type="submit" class="btn btn-primary">Search title</button>
                </form>
            </div>

            <div class="card card-border bg-base-100 shadow">
                <form method="GET" action="{{ route('web.comm-links.index') }}" class="card-body gap-4">
                    <input type="hidden" name="search" value="media-url">

                    <div class="flex flex-col gap-1">
                        <h2 class="card-title">Media URL</h2>
                        <p class="text-sm text-base-content/70">Find comm-links that reference a specific RSI-hosted image URL.</p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="input w-full">
                            <span class="label">Image URL</span>
                            <input
                                type="url"
                                name="url"
                                value="{{ old('url') }}"
                                placeholder="https://robertsspaceindustries.com/media/..."
                                pattern="http?s:\/\/(?:media\.)?robertsspaceindustries.com\/.*"
                                required
                            />
                        </label>
                        <span class="label-text-alt text-base-content/60">
                            Use https://robertsspaceindustries.com/media/... or https://media.robertsspaceindustries.com/...
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary">Search media URL</button>
                </form>
            </div>

            <div class="card card-border bg-base-100 shadow">
                <form method="GET" action="{{ route('web.comm-links.images.search') }}" class="card-body gap-4">

                    <div class="flex flex-col gap-1">
                        <h2 class="card-title">Media Name</h2>
                        <p class="text-sm text-base-content/70">Search comm-link images by filename.</p>
                    </div>

                    <label class="input w-full">
                        <span class="label">Filename contains</span>
                        <input
                            type="text"
                            name="query"
                            value="{{ old('query') }}"
                            placeholder="Carrack"
                            required
                        />
                    </label>

                    <button type="submit" class="btn btn-primary">Search media name</button>
                </form>
            </div>

            <div class="card card-border bg-base-100 shadow">
                <form
                    method="POST"
                    action="{{ route('web.comm-links.images.reverse-search') }}"
                    enctype="multipart/form-data"
                    class="card-body gap-4"
                >
                    @csrf

                    <div class="flex flex-col gap-1">
                        <h2 class="card-title">Reverse Image Search</h2>
                        <p class="text-sm text-base-content/70">Upload an image to find similar comm-link visuals.</p>
                    </div>

                    <div class="flex flex-col gap-1 w-full">
                        <span class="label">Image file</span>
                        <input type="file" name="image" accept="image/*" class="file-input w-full" required />
                        <span class="label-text-alt text-base-content/60">Max file size 5 MB.</span>
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
                        <span class="label-text-alt text-base-content/60">Lower values return more results.</span>
                    </div>

                    <button type="submit" class="btn btn-primary">Search by image</button>
                </form>
            </div>
        </div>
    </div>
@endsection
