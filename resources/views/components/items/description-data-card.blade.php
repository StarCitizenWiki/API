@props([
    'descriptionData',
])

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <h2 class="card-title text-base">Description Data</h2>

        @if (is_array($descriptionData) && $descriptionData !== [])
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <caption class="sr-only">Structured description data</caption>
                    <thead>
                        <tr>
                            <th scope="col">Key</th>
                            <th scope="col">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($descriptionData as $datum)
                            <tr>
                                <td class="whitespace-nowrap">{{ $datum['name'] ?? '-' }}</td>
                                <td>{{ $datum['value'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-sm text-subtle">No structured description data available.</div>
        @endif
    </div>
</section>
