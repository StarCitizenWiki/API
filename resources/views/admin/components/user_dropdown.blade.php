@component('components.forms.form-group', [
    'inputType' => 'select',
    'inputClass' => 'custom-select w-100',
    'id' => 'user_id',
    'inputOptions' => 'spellcheck=false',
])
    @slot('tabIndex')
        {{ $tabIndex or '' }}
    @endslot

    @slot('required')
        {{ $required or '' }}
    @endslot

    @slot('label')
        {{ $label or '' }}
    @endslot

    @slot('selectOptions')
        @forelse(\App\Models\User::all() as $user)
            <option value="{{ $user->getRouteKey() }}" @if($selectedID == $user->id) selected @endif>{{ $user->name }}</option>
        @empty
            <option value="1">Default</option>
        @endforelse
    @endslot
@endcomponent