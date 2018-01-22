<div class="btn-group btn-group-sm" role="group" aria-label="">
    @unless(empty($edit_url))
        <a href="{{ $edit_url or '' }}" class="btn btn-outline-secondary">
            @component('components.elements.icon')
                pencil
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
                trash
            @endcomponent
        </a>
    @endunless
    @unless(empty($restore_url))
        <a href="#" class="btn btn-outline-success" onclick="event.preventDefault(); document.getElementById('restore-form{{ $slot }}').submit();">
            @component('components.forms.form', [
                'id' => 'restore-form'.$slot,
                'class' => 'd-none',
                'action' => $restore_url,
            ])@endcomponent
            @component('components.elements.icon', [
                'class' => 'fa-flip-horizontal',
            ])
                repeat
            @endcomponent
        </a>
    @endunless
</div>