@extends('user.layouts.default_wide')

@section('title', __('Bild').' - '.$image->id. ' ' . __('(bearbeiten)'))

@section('content')
    <a href="{{ route('web.user.rsi.comm-links.images.start-edit') }}" class="btn btn-block btn-secondary mb-3">@lang('Anderes Bild')</a>
    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.user.rsi.comm-links.images.save-tags', $image->getRouteKey()),
        'class' => 'card',
    ])
        <div class="wrapper">
            <div class="card-body">
                @include('components.errors')
                <div class="row">
                    <div class="col-12 col-md-8">
                        @include('user.rsi.comm_links.components.image_info_card', ['image' => $image, 'noFooter' => true])
                    </div>
                    <div class="col-12 col-md-4 d-flex align-content-stretch flex-column">
                        <button type="submit" class="btn btn-block btn-primary mb-3">@lang('Speichern')</button>
                        <span class="help-block d-block mb-2">@lang('Neue Einträge können durch Tippen in der Auswahl hinzugefügt werden.')</span>
                        <select class="form-select custom-select form-control" multiple size="15" name="tags[]" id="tags">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" @php if ($image_tags->contains($tag->name)) echo "selected"; @endphp>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
@endsection


@section('body__after')
    @parent
    <script>
        (()=>{
            $('#tags').select2({
                allowClear: true,
                closeOnSelect: false,
                tags: true
            });

            $('#tags').select2('open');
        })();
    </script>
@endsection