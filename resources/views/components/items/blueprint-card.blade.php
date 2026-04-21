@props([
    'blueprint',
])

@php
    $blueprintUuid = data_get($blueprint, 'uuid');
    $blueprintWebUrl = data_get($blueprint, 'web_url');
    $craftTimeLabel = data_get($blueprint, 'craft_time_label');
    $isAvailableByDefault = data_get($blueprint, 'is_available_by_default');
    $ingredients = collect(data_get($blueprint, 'ingredients', []))
        ->filter(static fn (mixed $ing): bool => is_array($ing))
        ->values();

    $versionQuery = request()->query('version');
    $blueprintUrl = $blueprintWebUrl;

    if (is_string($blueprintUuid) && $blueprintUuid !== '') {
        $blueprintUrl = route('web.blueprints.show', array_filter([
            'blueprint' => $blueprintUuid,
            'version' => $versionQuery,
        ]));
    }

    $hasIngredients = $ingredients->isNotEmpty();
@endphp

<section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow', 'data-testid' => 'item-blueprint-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <h2 class="card-title text-base">
            @if (is_string($blueprintUrl) && $blueprintUrl !== '')
                <a href="{{ $blueprintUrl }}" class="link link-hover link-primary" data-testid="item-blueprint-card-link">Blueprint</a>
            @else
                Blueprint
            @endif
        </h2>

        <div class="{{ $hasIngredients ? 'grid grid-cols-1 gap-6 sm:grid-cols-2' : '' }}">
            @if ($hasIngredients)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <caption class="sr-only">Blueprint ingredients</caption>
                        <thead>
                            <tr>
                                <th scope="col">Ingredient</th>
                                <th scope="col" class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ingredients as $ingredient)
                                @php
                                    $ingredientName = data_get($ingredient, 'name') ?? 'Unknown';
                                    $ingredientQuantity = data_get($ingredient, 'quantity_scu');
                                    $ingredientWebUrl = data_get($ingredient, 'web_url');
                                @endphp

                                <tr>
                                    <td>
                                        @if (is_string($ingredientWebUrl) && $ingredientWebUrl !== '')
                                            <a href="{{ $ingredientWebUrl }}" class="link link-hover link-primary">{{ $ingredientName }}</a>
                                        @else
                                            {{ $ingredientName }}
                                        @endif
                                    </td>
                                    <td class="text-right text-base-content/70">
                                        @if ($ingredientQuantity !== null && $ingredientQuantity > 0)
                                            {{ $ingredientQuantity }} SCU
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <dl class="space-y-4">
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Craft Time</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $craftTimeLabel ?? '—' }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Availability</dt>
                    <dd>
                        @if ($isAvailableByDefault === true)
                            <span class="badge badge-success badge-sm badge-outline">Default</span>
                        @elseif ($isAvailableByDefault === false)
                            <span class="badge badge-warning badge-sm badge-outline">Unlock required</span>
                        @else
                            <span class="text-sm font-semibold text-base-content">—</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</section>
