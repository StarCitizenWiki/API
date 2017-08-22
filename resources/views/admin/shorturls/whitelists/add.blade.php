@extends('admin.layouts.default')

@section('content')
    <div class="card">
        <h4 class="card-header">__LOC__Add Whitelist</h4>
        <div class="card-body">
            @include('components.errors')
            @component('components.forms.form', [
                'action' => route('admin_urls_whitelist_add'),
            ])
                @component('components.forms.form-group', [
                    'inputType' => 'url',
                    'id' => 'url',
                    'value' => old('url'),
                    'required' => 1,
                    'autofocus' => 1,
                    'label' => '__LOC__URL',
                ])@endcomponent
                <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="internal" id="internal">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Intern</span>
                </label>
                <br>
                <button class="btn btn-outline-secondary" name="save">Speichern</button>
            @endcomponent
        </div>
    </div>
@endsection