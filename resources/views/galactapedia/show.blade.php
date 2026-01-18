@extends('layouts.app')

@php
    $pageTitleDecoded = html_entity_decode(html_entity_decode($pageTitle));
@endphp

@section('title')
    {!! $pageTitleDecoded !!} - Galactapedia Article
@endsection
@section('meta_description', "{$pageTitle} Galactapedia article details.")

@section('content')
    @php
        $articleTitle = data_get($article, 'title', 'Galactapedia Article');
        $articleId = data_get($article, 'id');
        $articleSlug = data_get($article, 'slug');
        $template = data_get($article, 'template');
        $thumbnail = data_get($article, 'thumbnail');
        $rsiUrl = data_get($article, 'rsi_url');
        $apiUrl = data_get($article, 'api_url');
        $createdAtHuman = data_get($article, 'created_at_human');
        $createdAt = data_get($article, 'created_at');

        $categoryList = [];
        $categories = data_get($article, 'categories', []);
        $categoryString = data_get($article, 'category');
        if (is_array($categories) && $categories !== []) {
            $categoryList = collect($categories)
                ->pluck('name')
                ->filter()
                ->values()
                ->all();
        } elseif (is_string($categoryString) && $categoryString !== '') {
            $categoryList = collect(explode(',', $categoryString))
                ->map(static fn (string $entry): string => trim($entry))
                ->filter()
                ->values()
                ->all();
        }

        $tagList = [];
        $tags = data_get($article, 'tags', []);
        $tagString = data_get($article, 'tag');
        if (is_array($tags) && $tags !== []) {
            $tagList = collect($tags)
                ->pluck('name')
                ->filter()
                ->values()
                ->all();
        } elseif (is_string($tagString) && $tagString !== '') {
            $tagList = collect(explode(',', $tagString))
                ->map(static fn (string $entry): string => trim($entry))
                ->filter()
                ->values()
                ->all();
        }

        $properties = data_get($article, 'properties', []);
        $relatedArticles = data_get($article, 'related_articles', []);
        $translations = data_get($article, 'translations');
        if (is_string($translations) && $translations !== '') {
            $translations = ['en' => $translations];
        }
        if (! is_array($translations)) {
            $translations = [];
        }
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-base-content/70">
                <ul>
                    <li><a href="{{ route('web.galactapedia.index') }}">Galactapedia</a></li>
                    <li>{{ $articleTitle }}</li>
                </ul>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight">{{ $articleTitle }}</h1>
                @if ($template)
                    <span class="badge badge-outline">{{ $template }}</span>
                @endif
            </div>
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
                                        $label = is_string($locale) ? \App\Models\System\Language::LABEL_MAP[$locale]  : 'Translation '.$loop->iteration;
                                        $translationText = is_string($translation)
                                            ? $translation
                                            : json_encode($translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                    @endphp
                                    <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                        <input type="checkbox" />
                                        <div class="collapse-title text-sm font-semibold">{{ $label }}</div>
                                        <div class="collapse-content">
                                            @if ($translationText)
                                                <div class="text-sm leading-relaxed text-base-content/80 whitespace-pre-line">
                                                    {!! nl2br(e($translationText)) !!}
                                                </div>
                                            @else
                                                <div class="text-sm text-base-content/70">No content available.</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-base-content/70">No translations available.</div>
                        @endif
                    </div>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Properties</h2>
                        @if (is_array($properties) && $properties !== [])
                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Value</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($properties as $property)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $property['name'] ?? '-' }}</td>
                                            <td class="whitespace-pre-line">{{ $property['value'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-base-content/70">No properties available.</div>
                        @endif
                    </div>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Related Articles</h2>
                        @if (is_array($relatedArticles) && $relatedArticles !== [])
                            <div class="flex flex-col gap-2">
                                @foreach ($relatedArticles as $relatedArticle)
                                    @php
                                        $relatedId = $relatedArticle['id'] ?? null;
                                        $relatedTitle = $relatedArticle['title'] ?? 'Unknown Article';
                                        $relatedUrl = $relatedArticle['url'] ?? null;
                                    @endphp
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <span class="text-sm font-medium">{{ $relatedTitle }}</span>
                                        @if ($relatedId)
                                            <a href="{{ route('web.galactapedia.show', $relatedId) }}" class="link link-primary text-sm">View</a>
                                        @elseif ($relatedUrl)
                                            <a href="{{ $relatedUrl }}" class="link link-primary text-sm" target="_blank" rel="noreferrer">RSI</a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-base-content/70">No related articles available.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    @if ($thumbnail)
                        <figure class="px-6 pt-6">
                            <img src="{{ $thumbnail }}" alt="{{ $articleTitle }}" class="rounded-box w-full object-cover" loading="lazy">
                        </figure>
                    @endif
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Metadata</h2>
                        <dl class="grid gap-4">
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">CIG ID</dt>
                                <dd class="text-sm font-medium">{{ $articleId ?? '-' }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Slug</dt>
                                <dd class="text-sm font-medium">{{ $articleSlug ?? '-' }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Template</dt>
                                <dd class="text-sm font-medium">{{ $template ?? '-' }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Published</dt>
                                <dd class="text-sm font-medium">
                                    {{ $createdAtHuman ?? '-' }}
                                    @if ($createdAt)
                                        <span class="text-xs text-base-content/60">({{ $createdAt }})</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        <div class="flex flex-wrap gap-2">
                            @if ($rsiUrl)
                                <a class="btn btn-outline btn-sm" href="{{ config('services.rsi_url') }}{{ $rsiUrl }}" target="_blank" rel="noreferrer">RSI Article</a>
                            @endif
                            @if ($apiUrl)
                                <a class="btn btn-outline btn-sm" href="{{ $apiUrl }}" target="_blank" rel="noreferrer">API</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Categories</h2>
                        @if ($categoryList !== [])
                            <div class="flex flex-wrap gap-2">
                                @foreach ($categoryList as $category)
                                    <span class="badge badge-neutral">{{ $category }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-base-content/70">No categories available.</div>
                        @endif
                    </div>
                </div>

                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Tags</h2>
                        @if ($tagList !== [])
                            <div class="flex flex-wrap gap-2">
                                @foreach ($tagList as $tag)
                                    <span class="badge badge-outline">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-base-content/70">No tags available.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
