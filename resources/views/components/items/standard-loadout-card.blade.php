@props([
    'vehicles',
])

@php
    $count = is_array($vehicles) ? count($vehicles) : 0;
@endphp

<section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow', 'data-testid' => 'item-installed-vehicles-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex items-center gap-2">
            <h2 class="card-title text-base">Standard Loadout of</h2>
        </div>

        @if ($count > 0)
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="table table-sm">
                    <caption class="sr-only">Vehicles that have this item installed</caption>
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Manufacturer</th>
                            <th scope="col">Career</th>
                            <th scope="col">Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vehicles as $vehicle)
                            <tr>
                                <td class="whitespace-nowrap">
                                    @if ($vehicle['uuid'] ?? null)
                                        <a href="{{ route('web.vehicles.show', $vehicle['uuid']) }}" class="link link-primary">{{ $vehicle['name'] ?? '-' }}</a>
                                    @else
                                        {{ $vehicle['name'] ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($vehicle['manufacturer'] ?? null)
                                        {{ $vehicle['manufacturer']['name'] ?? $vehicle['manufacturer']['code'] ?? '-' }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td>{{ $vehicle['career'] ?? '-' }}</td>
                                <td>{{ $vehicle['role'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-sm text-subtle">No vehicles found with this item installed.</div>
        @endif
    </div>
</section>
