<details class="collapse collapse-arrow bg-base-100 border-base-300 border">
    <summary class="collapse-title font-semibold flex justify-between">
        <span>Column source map</span>
        <span class="badge badge-outline ml-auto align-middle">
            {{ count($mappings) }} columns
        </span>
    </summary>

    <div class="collapse-content">
        <p class="text-sm text-subtle mb-3">
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
</details>
