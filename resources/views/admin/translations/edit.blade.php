@extends('admin.layout')

@section('breadcrumbs')
    <li><a href="{{ route('admin.translations.index') }}">Translations</a></li>
    <li>Edit</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.translations.index') }}" class="btn btn-ghost btn-sm">
                <x-icon name="arrow-left" class="size-4" />
            </a>
            <h1 class="text-2xl font-semibold" data-testid="translation-edit-heading">Edit Translations</h1>
        </div>

        <section class="space-y-4">
            <div class="card card-border bg-base-100 shadow">
                <div class="card-body p-5 sm:p-6">
                    <div class="mb-4" data-testid="translation-edit-meta">
                        <h2 class="text-lg font-semibold tracking-tight">{{ $model->title }}</h2>
                        <p class="text-sm text-subtle">ID: {{ $model->id }} | Type: {{ ucfirst(str_replace('-', ' ', $type)) }}</p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.translations.update', ['type' => $type, 'id' => $model->id]) }}"
                        data-testid="translation-edit-form"
                    >
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col gap-6">
                            {{-- English Translation --}}
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">{{ \App\Models\System\Language::LABEL_MAP[\App\Models\System\Language::ENGLISH] }}</span>
                                    <span class="label-text-alt">{{ \App\Models\System\Language::ENGLISH }}</span>
                                </label>
                                <textarea
                                    name="translations[{{ \App\Models\System\Language::ENGLISH }}]"
                                    data-testid="translation-edit-field-{{ \App\Models\System\Language::ENGLISH }}"
                                    class="textarea textarea-bordered w-full"
                                    rows="10"
                                    placeholder="Enter English translation..."
                                >{{ old('translations.'.\App\Models\System\Language::ENGLISH, $translations[\App\Models\System\Language::ENGLISH]) }}</textarea>
                            </div>

                            {{-- German Translation --}}
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">{{ \App\Models\System\Language::LABEL_MAP[\App\Models\System\Language::GERMAN] }}</span>
                                    <span class="label-text-alt">{{ \App\Models\System\Language::GERMAN }}</span>
                                </label>
                                <textarea
                                    name="translations[{{ \App\Models\System\Language::GERMAN }}]"
                                    data-testid="translation-edit-field-{{ \App\Models\System\Language::GERMAN }}"
                                    class="textarea textarea-bordered w-full"
                                    rows="10"
                                    placeholder="Enter German translation..."
                                >{{ old('translations.'.\App\Models\System\Language::GERMAN, $translations[\App\Models\System\Language::GERMAN]) }}</textarea>
                            </div>

                            {{-- Chinese Translation --}}
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">{{ \App\Models\System\Language::LABEL_MAP[\App\Models\System\Language::CHINESE] }}</span>
                                    <span class="label-text-alt">{{ \App\Models\System\Language::CHINESE }}</span>
                                </label>
                                <textarea
                                    name="translations[{{ \App\Models\System\Language::CHINESE }}]"
                                    data-testid="translation-edit-field-{{ \App\Models\System\Language::CHINESE }}"
                                    class="textarea textarea-bordered w-full"
                                    rows="10"
                                    placeholder="Enter Chinese translation..."
                                >{{ old('translations.'.\App\Models\System\Language::CHINESE, $translations[\App\Models\System\Language::CHINESE]) }}</textarea>
                            </div>

                            {{-- French Translation --}}
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">{{ \App\Models\System\Language::LABEL_MAP[\App\Models\System\Language::FRENCH] }}</span>
                                    <span class="label-text-alt">{{ \App\Models\System\Language::FRENCH }}</span>
                                </label>
                                <textarea
                                    name="translations[{{ \App\Models\System\Language::FRENCH }}]"
                                    data-testid="translation-edit-field-{{ \App\Models\System\Language::FRENCH }}"
                                    class="textarea textarea-bordered w-full"
                                    rows="10"
                                    placeholder="Enter French translation..."
                                >{{ old('translations.'.\App\Models\System\Language::FRENCH, $translations[\App\Models\System\Language::FRENCH]) }}</textarea>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary" data-testid="translation-edit-submit">
                                    Save Translations
                                </button>
                                <a href="{{ route('admin.translations.index') }}" class="btn btn-ghost">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
