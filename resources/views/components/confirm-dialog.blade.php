@props([
    'id' => null,
    'title' => 'Confirm Action',
    'message' => 'Are you sure?',
    'action' => null,
    'method' => 'POST',
    'confirmLabel' => 'Confirm',
    'confirmClass' => 'btn btn-error',
    'testId' => null,
])

<dialog
    @if($id) id="{{ $id }}" @endif
    class="modal"
    @if($testId) data-testid="{{ $testId }}" @endif
>
    <div class="modal-box">
        <h3 class="text-lg font-semibold">{{ $title }}</h3>
        <p class="py-4">{{ $message }}</p>
        <div class="modal-action">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if(strtoupper($method) !== 'GET')
                    @method($method)
                @endif
                <button type="submit" class="{{ $confirmClass }}">{{ $confirmLabel }}</button>
            </form>
            <button class="btn" onclick="{{ $id }}.close()">Cancel</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
