@php use App\Support\Format; @endphp
@extends('admin.layout')

@section('breadcrumbs')
    <li>Translations</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-semibold" data-testid="translation-index-heading">Translations Management</h1>

        <section class="space-y-4">
            <div class="card card-border bg-base-100 shadow">
                <div class="card-body p-0">
                    <div role="tablist" class="tabs tabs-lifted">
                        {{-- CommLinks Tab --}}
                        <input type="radio" name="translation_tabs" role="tab" class="tab" aria-label="CommLinks"
                               @if(!request()->has('articles_page')) checked @endif />
                        <div role="tabpanel" class="tab-content rounded-box border-base-300 bg-base-100 p-6"
                             data-testid="translation-index-comm-links-panel">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold tracking-tight">Comm-Links</h2>
                                    <span class="text-sm text-subtle">Total: {{ Format::number($commLinks->total()) }}</span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Created</th>
                                            <th>Translation Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($commLinks as $commLink)
                                            <tr>
                                                <td><a class="link" href="{{ route('web.comm-links.show', $commLink->cig_id) }}"
                                                       target="_blank">{{$commLink->cig_id}}</a></td>
                                                <td><a class="link" href="{{ route('web.comm-links.show', $commLink->cig_id) }}"
                                                       target="_blank">{{$commLink->title}}</a></td>
                                                <td>{{ $commLink->created_at->diffForHumans() }}</td>
                                                <td>
                                                    @php
                                                        $translations = $commLink->getTranslations('translation');
                                                    @endphp
                                                    <div class="flex gap-1">
                                                        <span
                                                            class="badge {{ isset($translations['en']) && !empty($translations['en']) ? 'badge-success' : 'badge-soft' }}">
                                                                EN
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['de']) && !empty($translations['de']) ? 'badge-success' : 'badge-soft' }}">
                                                                DE
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['zh']) && !empty($translations['zh']) ? 'badge-success' : 'badge-soft' }}">
                                                                ZH
                                                            </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a
                                                        data-testid="translation-index-edit-link-comm-link-{{ $commLink->id }}"
                                                        href="{{ route('admin.translations.edit', ['type' => 'comm-link', 'id' => $commLink->id]) }}"
                                                        class="btn btn-outline btn-sm"
                                                    >
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-subtle">No comm-links found.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($commLinks->hasPages())
                                    <div class="flex justify-center">
                                        {{ $commLinks->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Articles Tab --}}
                        <input type="radio" name="translation_tabs" role="tab" class="tab" aria-label="Galctapedia"
                               @if(request()->has('articles_page')) checked @endif />
                        <div role="tabpanel" class="tab-content rounded-box border-base-300 bg-base-100 p-6"
                             data-testid="translation-index-articles-panel">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold tracking-tight">Articles</h2>
                                    <span class="text-sm text-subtle">Total: {{ Format::number($articles->total()) }}</span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Created</th>
                                            <th>Translation Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($articles as $article)
                                            <tr>
                                                <td><a href="{{ route('web.galactapedia.show', $article->cig_id) }}"
                                                       target="_blank">{{ $article->id }}</a></td>
                                                <td><a href="{{ route('web.galactapedia.show', $article->cig_id) }}"
                                                       target="_blank">{{ $article->title }}</a></td>
                                                <td>{{ $article->created_at->diffForHumans() }}</td>
                                                <td>
                                                    @php
                                                        $translations = $article->getTranslations('translation');
                                                    @endphp
                                                    <div class="flex gap-1">
                                                        <span
                                                            class="badge {{ isset($translations['en']) && !empty($translations['en']) ? 'badge-success' : 'badge-soft' }}">
                                                                EN
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['de']) && !empty($translations['de']) ? 'badge-success' : 'badge-soft' }}">
                                                                DE
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['zh']) && !empty($translations['zh']) ? 'badge-success' : 'badge-soft' }}">
                                                                ZH
                                                            </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a
                                                        data-testid="translation-index-edit-link-article-{{ $article->id }}"
                                                        href="{{ route('admin.translations.edit', ['type' => 'article', 'id' => $article->id]) }}"
                                                        class="btn btn-outline btn-sm"
                                                    >
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-subtle">No articles found.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($articles->hasPages())
                                    <div class="flex justify-center">
                                        {{ $articles->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Ship-Matrix Tab --}}
                        <input type="radio" name="translation_tabs" role="tab" class="tab" aria-label="Ship-Matrix"/>
                        <div role="tabpanel" class="tab-content rounded-box border-base-300 bg-base-100 p-6"
                             data-testid="translation-index-ship-matrix-panel">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold tracking-tight">Ship-Matrix</h2>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Slug</th>
                                            <th>Translation Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        {{-- Sizes --}}
                                        <thead>
                                        <tr>
                                            <th colspan="4">Sizes</th>
                                        </tr>
                                        </thead>
                                        @forelse ($smSizes as $size)
                                            <tr>
                                                <td>{{ $size->id }}</td>
                                                <td>{{ $size->slug }}</td>
                                                <td>
                                                    @php
                                                        $translations = $size->getTranslations('translation');
                                                    @endphp
                                                    <div class="flex gap-1">
                                                        <span
                                                            class="badge {{ isset($translations['en']) && !empty($translations['en']) ? 'badge-success' : 'badge-soft' }}">
                                                                EN
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['de']) && !empty($translations['de']) ? 'badge-success' : 'badge-soft' }}">
                                                                DE
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['zh']) && !empty($translations['zh']) ? 'badge-success' : 'badge-soft' }}">
                                                                ZH
                                                            </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a
                                                        data-testid="translation-index-edit-link-smSize-{{ $size->id }}"
                                                        href="{{ route('admin.translations.edit', ['type' => 'smSize', 'id' => $size->id]) }}"
                                                        class="btn btn-outline btn-sm"
                                                    >
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-subtle">No Sizes found.</td>
                                            </tr>
                                        @endforelse

                                        {{-- Focuses --}}
                                        <thead>
                                        <tr>
                                            <th colspan="4">Focuses</th>
                                        </tr>
                                        </thead>
                                        @forelse ($smFocuses as $focus)
                                            <tr>
                                                <td>{{ $focus->id }}</td>
                                                <td>{{ $focus->slug }}</td>
                                                <td>
                                                    @php
                                                        $translations = $focus->getTranslations('translation');
                                                    @endphp
                                                    <div class="flex gap-1">
                                                        <span
                                                            class="badge {{ isset($translations['en']) && !empty($translations['en']) ? 'badge-success' : 'badge-soft' }}">
                                                                EN
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['de']) && !empty($translations['de']) ? 'badge-success' : 'badge-soft' }}">
                                                                DE
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['zh']) && !empty($translations['zh']) ? 'badge-success' : 'badge-soft' }}">
                                                                ZH
                                                            </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a
                                                        data-testid="translation-index-edit-link-smFocus-{{ $focus->id }}"
                                                        href="{{ route('admin.translations.edit', ['type' => 'smFocus', 'id' => $focus->id]) }}"
                                                        class="btn btn-outline btn-sm"
                                                    >
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-subtle">No Focuses found.</td>
                                            </tr>
                                        @endforelse

                                        {{-- Types --}}
                                        <thead>
                                        <tr>
                                            <th colspan="4">Types</th>
                                        </tr>
                                        </thead>
                                        @forelse ($smTypes as $type)
                                            <tr>
                                                <td>{{ $type->id }}</td>
                                                <td>{{ $type->slug }}</td>
                                                <td>
                                                    @php
                                                        $translations = $type->getTranslations('translation');
                                                    @endphp
                                                    <div class="flex gap-1">
                                                        <span
                                                            class="badge {{ isset($translations['en']) && !empty($translations['en']) ? 'badge-success' : 'badge-soft' }}">
                                                                EN
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['de']) && !empty($translations['de']) ? 'badge-success' : 'badge-soft' }}">
                                                                DE
                                                            </span>
                                                        <span
                                                            class="badge {{ isset($translations['zh']) && !empty($translations['zh']) ? 'badge-success' : 'badge-soft' }}">
                                                                ZH
                                                            </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a
                                                        data-testid="translation-index-edit-link-smType-{{ $type->id }}"
                                                        href="{{ route('admin.translations.edit', ['type' => 'smType', 'id' => $type->id]) }}"
                                                        class="btn btn-outline btn-sm"
                                                    >
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-subtle">No Types found.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
