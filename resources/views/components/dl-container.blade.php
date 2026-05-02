@props([
    'title' => null,
    'dlClass' => 'space-y-2',
])

@if($title)
    <section {{ $attributes->merge(['class' => 'space-y-3']) }}>
        <h3 class="font-semibold uppercase text-subtle">{{ $title }}</h3>
@endif
        <dl class="{{ $dlClass }}">
            {{ $slot }}
        </dl>
@if($title)
    </section>
@endif
