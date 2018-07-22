@extends('admin.layouts.default')

@section('content')
    @component('components.forms.form', [
        'action' => route('web.admin.starcitizen.vehicles.sizes.update', $size->id),
        'method' => 'PATCH',
    ])
        <div class="card">
            <h4 class="card-header">@lang('Übersetzungen')</h4>
            <div class="card-body">

                    @foreach($size->translationsCollection() as $key => $translation)
                        @component('components.forms.form')
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'label' => __('Übersetzung ').$key,
                                'id' => 'translation_'.$key,
                                'value' => $translation->translation,
                            ])
                                @slot('inputOptions')
                                    @if($key === config('language.english'))
                                        readonly
                                    @endif
                                @endslot
                            @endcomponent
                        @endcomponent
                    @endforeach
            </div>
            <div class="card-footer">
                <button class="btn btn-outline-success" name="save">@lang('Speichern')</button>
            </div>
        </div>
    @endcomponent
@endsection
