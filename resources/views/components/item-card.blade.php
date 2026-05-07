@props([
    'title',
])

@if($slot->hasActualContent())
<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base -mb-2">{{ $title }}</h2>
        {{ $slot }}
    </div>
</div>
@endif
