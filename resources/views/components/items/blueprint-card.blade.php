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

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow', 'data-testid' => 'item-blueprint-card']) }}>
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
                                    $ingredientQuantityScu = data_get($ingredient, 'quantity_scu');
                                    $ingredientQuantity = data_get($ingredient, 'quantity');
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
                                    <td class="text-right text-subtle">
                                        @if ($ingredientQuantity !== null && $ingredientQuantity > 0)
                                            {{ $ingredientQuantity }}×
                                        @elseif ($ingredientQuantityScu !== null && $ingredientQuantityScu > 0)
                                            {{ $ingredientQuantityScu }} SCU
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

            <x-dl-section dlClass="space-y-4">
                <x-dt-dd label="Craft Time" :value="$craftTimeLabel">{{ $craftTimeLabel }}</x-dt-dd>
                <x-dt-dd label="Availability" :value="$isAvailableByDefault">
                    @if ($isAvailableByDefault === true)
                        <span class="badge badge-success badge-sm badge-outline">Default</span>
                    @else
                        <span class="badge badge-warning badge-sm badge-outline">Unlock required</span>
                    @endif
                </x-dt-dd>
            </x-dl-section>
        </div>
    </div>
</section>
