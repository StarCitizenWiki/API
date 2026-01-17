<div class="card card-border bg-base-100">
    <div class="card-body gap-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="card-title text-base">Column source map</h2>
            <span class="badge badge-outline">{{ count($mappings) }} columns</span>
        </div>
        <p class="text-sm text-base-content/70">
            Field corresponds to key under data json output from API.
        </p>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Field</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mappings as $mapping)
                        <tr>
                            <td class="font-medium">{{ $mapping['title'] }}</td>
                            <td>
                                <code class="rounded bg-base-200 px-2 py-1 text-xs">{{ $mapping['field'] }}</code>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
