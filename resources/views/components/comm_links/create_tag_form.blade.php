@component('components.forms.form', [
    'action' => route('web.rsi.comm-links.image-tags.create'),
    'method' => 'POST',
    'class' => 'card mb-4'
])
    <div class="wrapper">
        <h4 class="card-header">@lang('Tag Erstellen')</h4>
        <div class="card-body">
            @include('components.errors')
            @include('components.messages')
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="name" id="name" placeholder="Name" aria-label="Name" aria-describedby="tag-name">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">@lang('Speichern')</button>
                </div>
            </div>
        </div>
    </div>
@endcomponent