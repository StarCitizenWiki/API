<div class="card border border-base-300 bg-base-100 shadow">
    <form method="GET" action="{{ route('web.items.index') }}" class="card-body gap-4">
        <div class="flex flex-col gap-2">
            <h2 class="card-title text-base">Search items</h2>
            <p class="text-sm text-base-content/70">Find items by name across the universe database.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="input input-bordered flex w-full items-center gap-2">
                <x-icon name="search" class="size-4 text-base-content/60" />
                <input
                    type="search"
                    name="filter[name]"
                    class="w-full"
                    placeholder="Search item names"
                />
            </label>
            <button class="btn btn-primary sm:shrink-0" type="submit">Search</button>
        </div>
    </form>
</div>
