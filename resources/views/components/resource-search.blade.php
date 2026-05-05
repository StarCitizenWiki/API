@props([
    'title',
    'description' => null,
    'route',
    'placeholder',
    'variant' => 'prominent',
    'apiEndpoint',
    'helpText' => null,
])

@if ($variant === 'minimal')
    <div data-remove {{ $attributes->merge(['class' => 'rounded-box border border-base-300 bg-base-200 p-3 sm:p-4']) }}>
        <form method="GET" action="{{ $route }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 space-y-1 lg:max-w-sm xl:max-w-md">
                <h2 class="text-sm font-semibold tracking-tight text-emphasis">{{ $title }}</h2>
                @if ($description)
                    <p class="text-xs leading-5 text-subtle">{{ $description }}</p>
                @endif
            </div>
            <div class="join join-vertical w-full sm:join-horizontal lg:max-w-xl">
                <label class="input input-bordered join-item flex w-full items-center gap-2 bg-base-100">
                    <x-icon name="search" class="size-4 text-muted" />
                    <input
                        type="search"
                        name="filter[name]"
                        class="grow text-sm"
                        placeholder="{{ $placeholder }}"
                        data-live-search
                        data-api-endpoint="{{ $apiEndpoint }}"
                        autocomplete="off"
                    />
                </label>
                <button class="btn btn-secondary join-item px-4 sm:shrink-0" type="submit">Search</button>
            </div>
        </form>
    </div>
@else
    <div data-remove {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <form method="GET" action="{{ $route }}" class="card-body gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="card-title text-base">{{ $title }}</h2>
                @if ($description)
                    <p class="text-sm text-subtle">{{ $description }}</p>
                @endif
            </div>
            <fieldset class="fieldset">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <label class="input input-bordered flex w-full items-center gap-2">
                        <x-icon name="search" class="size-4 text-subtle" />
                        <input
                            type="search"
                            name="filter[name]"
                            class="w-full"
                            placeholder="{{ $placeholder }}"
                            data-live-search
                            data-api-endpoint="{{ $apiEndpoint }}"
                            autocomplete="off"
                        />
                    </label>
                    <button class="btn btn-primary sm:shrink-0" type="submit">Search</button>
                </div>
                @if ($helpText)
                    <p class="text-xs text-accent-content/40">{!! $helpText !!}</p>
                @endif
            </fieldset>
        </form>
    </div>
@endif
