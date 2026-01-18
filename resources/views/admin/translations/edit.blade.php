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
            <h1 class="text-2xl font-bold">Edit Translations</h1>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">{{ $model->title }}</h2>
                    <p class="text-sm text-base-content/60">ID: {{ $model->id }} | Type: {{ ucfirst(str_replace('-', ' ', $type)) }}</p>
                </div>

                <form method="POST" action="{{ route('admin.translations.update', ['type' => $type, 'id' => $model->id]) }}">
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
                                class="textarea textarea-bordered w-full"
                                rows="10"
                                placeholder="Enter Chinese translation..."
                            >{{ old('translations.'.\App\Models\System\Language::CHINESE, $translations[\App\Models\System\Language::CHINESE]) }}</textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary">
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
    </div>
@endsection
