@foreach($componentGroups as $key => $components)
    <div>
        <h5>{{ $key }}</h5>
        <ul style="column-count: 2">
            @foreach($components as $component)
                <li class="mb-4">{{ $component->name }}
                    <ul>
                        <li>Type: {{ $component->type }}</li>
                        <li>Size: {{ $component->size }}</li>
                        <li>Mounts: {{ $component->mounts }}</li>
                        <li>Manufacturer: {{ $component->manufacturer }}</li>
                        <li>Quantity: {{ $component->quantity }}</li>
                        <li>Details: {{ $component->details }}</li>
                    </ul>
                </li>
            @endforeach
        </ul>
        <hr>
    </div>
@endforeach