@props([
    'descriptionData',
])

<section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <h2 class="card-title text-base">Description Data</h2>

        @if (is_array($descriptionData) && $descriptionData !== [])
            <div class="grid gap-2 sm:hidden">
                @foreach ($descriptionData as $datum)
                    <div class="card border border-base-300 bg-base-100 shadow-sm">
                        <div class="card-body gap-2 p-3">
                            <div class="text-xs font-medium uppercase tracking-wide text-muted">
                                {{ $datum['name'] ?? '-' }}
                            </div>
                            <div class="text-sm font-medium wrap-break-word">
                                {{ $datum['value'] ?? '-' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden overflow-x-auto sm:block">
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
