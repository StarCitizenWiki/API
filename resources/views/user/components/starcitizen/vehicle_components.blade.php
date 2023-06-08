@foreach($componentGroups as $key => $components)
    <div>
        <h5>{{ $key }}</h5>
        <ul style="column-count: 2">
            @foreach($components as $component)
                <li class="mb-4">{{ $component->name }}
                    <ul>
                        <li>@lang('Typ'): {{ $component->type }}</li>
                        <li>@lang('Größe'): {{ $component->size }}</li>
                        <li>@lang('Montiert'): {{ $component->mounts }}</li>
                        <li>@lang('Hersteller'): {{ $component->manufacturer }}</li>
                        <li>@lang('Menge'): {{ $component->quantity }}</li>
                        <li>@lang('Details'): {{ $component->details }}</li>
                    </ul>
                </li>
            @endforeach
        </ul>
        <hr>
    </div>
@endforeach