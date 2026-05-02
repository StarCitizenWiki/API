@props([
    'title' => null,
    'dlClass' => '',
])

@if($slot->hasActualContent())
@if($title)
    <section>
        <h3 class="font-semibold uppercase text-subtle mb-0 pb-0">{{ $title }}</h3>
@endif
        <dl class="{{ $dlClass }}">
            {{ $slot }}
        </dl>
@if($title)
    </section>
@endif
@endif
