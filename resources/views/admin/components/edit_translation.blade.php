@component('components.forms.form', [
    'action' => route($updateRoute, $translation->id),
    'method' => 'PATCH',
    'class' => 'card h-100 d-flex flex-column justify-content-between'
])
    <div class="wrapper">
        <h4 class="card-header">@lang('Übersetzungen')</h4>
        <div class="card-body">
            @foreach($translation->translationsCollection() as $key => $translationObject)
                @component('components.forms.form-group', [
                    'inputType' => 'text',
                    'label' => __('Übersetzung ').$key,
                    'id' => $key,
                    'value' => $translationObject->translation,
                ])
                    @slot('inputOptions')
                        @if($key === config('language.english'))
                            readonly
                        @endif
                    @endslot
                @endcomponent
            @endforeach
        </div>
        <div class="card-footer">
            <button class="btn btn-outline-success" name="save">@lang('Speichern')</button>
        </div>
    </div>
@endcomponent