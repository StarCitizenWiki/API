@props([
    'item',
    'type',
])

@php
    $fpsSpecCount = collect([
        $type === 'WeaponPersonal',
        $type === 'Armor',
        data_get($item, 'suit_armor'),
        data_get($item, 'temperature_resistance'),
        data_get($item, 'radiation_resistance'),
        data_get($item, 'ammunition'),
        data_get($item, 'weapon_modifier'),
        data_get($item, 'inventory') && data_get($item, 'inventory.unit') === 'µSCU',
    ])->filter()->count();
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }} open>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        <span class="flex items-center gap-2">
            <x-icon name="contact-round" class="size-5 text-primary" />
            <span>FPS Data</span>
            @if ($fpsSpecCount > 0)
                <span class="badge badge-ghost text-xs">{{ $fpsSpecCount }}</span>
            @endif
        </span>
    </summary>
    <div class="collapse-content">
        <div class="grid gap-4 lg:gap-6 grid-cols-1 lg:grid-cols-2">
            @if ($type === 'WeaponPersonal')
                <x-items.personal-weapon-card :personal-weapon="data_get($item, 'personal_weapon')" />
            @endif

            @if ($type === 'Armor')
                <x-items.armor-card :armor="data_get($item, 'armor')" />
            @endif

            @if ($type === 'WeaponAttachment')
                <x-items.weapon-attachment-card :weapon-attachment="$item" />
            @endif

            @if (data_get($item, 'suit_armor'))
                <x-items.suit-armor-card :suit-armor="data_get($item, 'suit_armor')" />
            @endif

            @if (data_get($item, 'temperature_resistance'))
                <x-items.temperature-resistance-card :temperature-resistance="data_get($item, 'temperature_resistance')" />
            @endif

            @if (data_get($item, 'radiation_resistance'))
                <x-items.radiation-resistance-card :radiation-resistance="data_get($item, 'radiation_resistance')" />
            @endif

            @if (data_get($item, 'ammunition'))
                <x-items.ammunition-card :ammunition="data_get($item, 'ammunition')" />
            @endif

            @if (data_get($item, 'weapon_modifier'))
                <x-items.weapon-modifier-card :weapon-modifier="data_get($item, 'weapon_modifier')" />
            @endif

            {{-- TODO: Hacky --}}
            @if (data_get($item, 'inventory') && data_get($item, 'inventory.unit') === 'µSCU' )
                <x-items.inventory-card :inventory="data_get($item, 'inventory')" />
            @endif
        </div>
    </div>
</details>
