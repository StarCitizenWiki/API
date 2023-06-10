<div class="btn-group btn-group-sm" role="group" aria-label="">
    @unless(empty($show_url))
        <a href="{{ $show_url ?? '' }}" class="btn btn-outline-primary" role="button">
            @component('components.elements.icon')
                @slot('options')
                    data-fa-transform="up-2"
                @endslot
                eye
            @endcomponent
        </a>
    @endunless
    @unless(empty($edit_url))
        <a href="{{ $edit_url ?? '' }}" class="btn btn-outline-secondary" role="button">
            @component('components.elements.icon')
                @slot('options')
                    data-fa-transform="up-2"
                @endslot
                pen
            @endcomponent
        </a>
    @endunless
    @unless(empty($delete_url))
        <a href="#" class="btn btn-outline-danger" onclick="event.preventDefault(); document.getElementById('delete-form{{ $slot }}').submit();">
            @component('components.forms.form', [
                'id' => 'delete-form'.$slot,
                'class' => 'd-none',
                'action' => $delete_url,
                'method' => 'DELETE',
            ])@endcomponent
            @component('components.elements.icon')
                @slot('options')
                    data-fa-transform="up-2"
                @endslot
                trash-alt
            @endcomponent
        </a>
    @endunless
    @unless(empty($restore_url))
        <a href="#" class="btn btn-outline-success" onclick="event.preventDefault(); document.getElementById('restore-form{{ $slot }}').submit();">
            @component('components.forms.form', [
                'id' => 'restore-form'.$slot,
                'class' => 'd-none',
                'action' => $restore_url,
                'method' => 'PATCH',
            ])
                <input type="hidden" id="restore" name="restore">
            @endcomponent
            @component('components.elements.icon', [
                'class' => 'fa-flip-horizontal',
            ])
                @slot('options')
                    data-fa-transform="up-2"
                @endslot
                redo
            @endcomponent
        </a>
    @endunless
</div>