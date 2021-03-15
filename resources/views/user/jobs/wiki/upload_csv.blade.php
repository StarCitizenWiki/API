@extends('user.layouts.default')

@section('title', __('CSV Hochladen'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('CVS Hochladen')</h4>
        <div class="card-body">
            @include('components.errors')
            @component('components.forms.form', [
                'action' => route('web.user.jobs.wiki.upload_csv'),
                'class' => 'mb-3',
                'enctype' => 'multipart/form-data'
            ])
                <div class="form-group">
                    <label for="file">GoogleDoc Tabelle in CSV Form</label>
                    <input type="file" class="form-control-file" id="file" name="file">
                </div>
                <button class="btn btn-block btn-outline-secondary">@lang('Eigenschaften importieren')</button>
            @endcomponent
        </div>
    </div>
@endsection
