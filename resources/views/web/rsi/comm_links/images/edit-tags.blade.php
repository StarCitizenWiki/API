@extends('web.layouts.default')

@section('title', __('Bild').' - '.$image->id. ' ' . __('(bearbeiten)'))

@section('content')
    <div class="d-flex mb-3 nav-bar justify-content-between">
        @unless($image->prev === null)
            <a href="{{ route('web.rsi.comm-links.images.edit-tags', $image->prev) }}" class="btn btn-outline-secondary d-block">@lang('Vorheriges Bild')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled d-block">@lang('Vorheriges Bild')</a>
        @endunless
        <a href="{{ route('web.rsi.comm-links.images.start-edit') }}" class="btn btn-secondary d-block">@lang('Zufälliges Bild')</a>
        @unless($image->next === null)
            <a href="{{ route('web.rsi.comm-links.images.edit-tags', $image->next) }}" class="btn btn-outline-secondary d-block">@lang('Nächstes Bild')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled d-block">@lang('Nächstes Bild')</a>
        @endunless
    </div>

    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.rsi.comm-links.images.save-tags', $image->getRouteKey()),
        'class' => '',
    ])
        @include('components.errors')
        <div class="row">
            <div class="col-12 col-md-7">
                @include('components.comm_links.image_info_card', ['image' => $image, 'noFooter' => true])
            </div>
            <div class="col-12 col-md-5 d-flex align-content-stretch flex-column">
                <button type="submit" class="btn btn-block btn-primary mb-3">@lang('Speichern')</button>
                <span class="alert alert-warning d-none" id="new-tag-warning"></span>
                <span class="help-block d-block mb-2">@lang('Neue Tags können durch Tippen in der Auswahl hinzugefügt werden.')</span>
                <select class="form-select custom-select form-control" multiple size="15" name="tags[]" id="tags">
                    @foreach($tags as $tag)
                        <option value="id:{{ $tag->id }}" @php if ($image_tags->contains($tag->name)) echo "selected"; @endphp>{{ $tag->translated_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endcomponent
@endsection


@section('body__after')
    @parent
    <script>
        (()=>{
            const newTags = [];

            $('#tags').select2({
                allowClear: true,
                closeOnSelect: false,
                tags: true
            });

            $('#tags').select2('open');

            $('#tags').on('select2:selecting', function(e) {
                const datum = e.params.args.data;

                if (datum?.id && (datum?.id ?? '').slice(0, 3) !== 'id:') {
                    if (!newTags.includes(datum.id)) {
                        newTags.push(datum.id);
                    }
                }

                if (newTags.length > 0) {
                    const container = document.querySelector('#new-tag-warning');
                    container.classList.remove('d-none');

                    container.innerText = `Die folgenden Tags existieren noch nicht in der Datenbank: ${newTags.join(', ')}.`
                }
            });
        })();
    </script>
@endsection