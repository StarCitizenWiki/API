@component('components.forms.form', [
    'action' => route($updateRoute, $translation->getRouteKey()),
    'method' => 'PATCH',
    'class' => 'card h-100 d-flex flex-column justify-content-between'
])
    <div class="wrapper">
        <h4 class="card-header">@lang('Übersetzungen')</h4>
        <div class="card-body">
            @include('components.errors')
            @include('components.messages')
            @foreach($translation->translationsCollection() as $key => $translationObject)
                @component('components.forms.form-group', [
                    'inputType' => 'text',
                    'label' => __('Übersetzung ').__($key),
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
            <button class="btn btn-outline-secondary ml-auto d-flex" name="save">@lang('Speichern')</button>
        </div>
    </div>
@endcomponent