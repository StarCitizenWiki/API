<x-app.sidemenu-group >
    <x-app.sidemenu-item :route="'home'">
        <x-slot:icon>
            <x-icon name="home" class="size-4" />
        </x-slot:icon>
        Home
    </x-app.sidemenu-item>
    @auth
        @can('access-admin')
            <x-app.sidemenu-item route="admin.dashboard" :with-version="false">
                <x-slot:icon>
                    <x-icon name="shield" class="size-4" />
                </x-slot:icon>
                Admin
            </x-app.sidemenu-item>
        @endcan
    @endauth
</x-app.sidemenu-group>

<x-app.sidemenu-group title="Comm-Link">
    <x-app.sidemenu-item
        :route="'web.comm-links.index'"
        :with-version="false"
    >
        <x-slot:icon>
            <x-icon name="satellite" class="size-4" />
        </x-slot:icon>
        Comm-Links
    </x-app.sidemenu-item>
    <x-app.sidemenu-item
        :route="'web.stats.index'"
        route-is="web.stats.*"
        :with-version="false"
    >
        <x-slot:icon>
            <x-icon name="line-chart" class="size-4" />
        </x-slot:icon>
        Stats
    </x-app.sidemenu-item>
</x-app.sidemenu-group>

<x-app.sidemenu-group title="Galactapedia">
    <x-app.sidemenu-item
        :route="'web.galactapedia.index'"
        route-is="web.galactapedia.*"
        :with-version="false"
    >
        <x-slot:icon>
            <x-icon name="book-open" class="size-4" />
        </x-slot:icon>
        Galactapedia
    </x-app.sidemenu-item>
</x-app.sidemenu-group>

<x-app.sidemenu-group title="Universe">
    <x-app.sidemenu-item
        :route="'web.vehicles.index'"
        route-is="web.vehicles.*"
    >
        <x-slot:icon>
            <x-icon name="rocket" class="size-4" />
        </x-slot:icon>
        Vehicles
    </x-app.sidemenu-item>
    <x-app.sidemenu-item
        :route="'web.items.index'"
        route-is="web.items.*"
    >
        <x-slot:icon>
            <x-icon name="package" class="size-4" />
        </x-slot:icon>
        Items
    </x-app.sidemenu-item>
</x-app.sidemenu-group>

<x-app.sidemenu-group title="Ship-Matrix">
    <x-app.sidemenu-item
        :route="'web.ship-matrix.vehicles.index'"
        route-is="web.ship-matrix.vehicles.*"
        :with-version="false"
    >
        <x-slot:icon>
            <x-icon name="rocket" class="size-4" />
        </x-slot:icon>
        Vehicles
    </x-app.sidemenu-item>
</x-app.sidemenu-group>

<x-app.sidemenu-group title="Starmap">
    <x-app.sidemenu-item
        :route="'web.starmap.systems.index'"
        route-is="web.starmap.systems.*"
        :with-version="false"
    >
        <x-slot:icon>
            <x-icon name="map" class="size-4" />
        </x-slot:icon>
        Systems
    </x-app.sidemenu-item>
    <x-app.sidemenu-item
        :route="'web.starmap.celestial-objects.index'"
        route-is="web.starmap.celestial-objects.*"
        :with-version="false"
    >
        <x-slot:icon>
            <x-icon name="star" class="size-4" />
        </x-slot:icon>
        Celestial Objects
    </x-app.sidemenu-item>
</x-app.sidemenu-group>



<x-app.sidemenu-group title="Explore">
    <x-app.sidemenu-item href="https://docs.star-citizen.wiki" :with-version="false">
        <x-slot:icon>
            <x-icon name="search" class="size-4" />
        </x-slot:icon>
        Api Documentation
    </x-app.sidemenu-item>
    <x-app.sidemenu-item href="https://github.com/StarCitizenWiki/API" :with-version="false">
        <x-slot:icon>
            <x-icon name="github" class="size-4" />
        </x-slot:icon>
        Source Code
    </x-app.sidemenu-item>
</x-app.sidemenu-group>
