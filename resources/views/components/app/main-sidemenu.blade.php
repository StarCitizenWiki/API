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
    <x-app.sidemenu-item :route="'web.items.index'" collapsible>
        <x-slot:icon>
            <x-icon name="package" class="size-4" />
        </x-slot:icon>
        Items

        <x-slot:children>
            <x-app.sidemenu-item
                :route="'web.items.index'"
            >
                <x-slot:icon>
                    <x-icon name="package" class="size-4" />
                </x-slot:icon>
                All Items
            </x-app.sidemenu-item>

            <div class="divider m-0"></div>

            <x-app.sidemenu-item
                :route="'web.items.type'"
                :params="['type' => 'WeaponPersonal']"
            >
                <x-slot:icon>
                    <x-icon name="crosshair" class="size-4" />
                </x-slot:icon>
                Personal Weapons
            </x-app.sidemenu-item>

            <x-app.sidemenu-item :route="'web.items.type'" :params="['type' => 'armor']">
                <x-slot:icon>
                    <x-icon name="shield" class="size-4" />
                </x-slot:icon>
                Armor
            </x-app.sidemenu-item>

            <x-app.sidemenu-item :route="'web.items.type'" :params="['type' => 'clothes']">
                <x-slot:icon>
                    <x-icon name="shirt" class="size-4" />
                </x-slot:icon>
                Clothes
            </x-app.sidemenu-item>

            <x-app.sidemenu-item :route="'web.items.type'" :params="['type' => 'food']">
                <x-slot:icon>
                    <x-icon name="utensils" class="size-4" />
                </x-slot:icon>
                Food & Drinks
            </x-app.sidemenu-item>

            <x-app.sidemenu-item :route="'web.items.type'" :params="['type' => 'weapon-attachments']">
                <x-slot:icon>
                    <x-icon name="puzzle-piece" class="size-4" />
                </x-slot:icon>
                Weapon Attachments
            </x-app.sidemenu-item>

{{--            <x-app.sidemenu-item :route="'web.items.type'">--}}
{{--                <x-slot:icon>--}}
{{--                    <x-icon name="shield" class="size-4" />--}}
{{--                </x-slot:icon>--}}
{{--                Armor--}}
{{--            </x-app.sidemenu-item>--}}
        </x-slot:children>
    </x-app.sidemenu-item>

    <x-app.sidemenu-item :route="'web.vehicle-items.index'" collapsible>
        <x-slot:icon>
            <x-icon name="cpu" class="size-4" />
        </x-slot:icon>
        Vehicle-Items

        <x-slot:children>
            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'Cooler']"
            >
                <x-slot:icon>
                    <x-icon name="fan" class="size-4" />
                </x-slot:icon>
                Coolers
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'PowerPlant']"
            >
                <x-slot:icon>
                    <x-icon name="plug" class="size-4" />
                </x-slot:icon>
                Power-Plants
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'QuantumDrive']"
            >
                <x-slot:icon>
                    <x-icon name="atom" class="size-4" />
                </x-slot:icon>
                Quantum Drives
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'Shield']"
            >
                <x-slot:icon>
                    <x-icon name="shield" class="size-4" />
                </x-slot:icon>
                Shields
            </x-app.sidemenu-item>

            <div class="divider m-0"></div>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'FlightController']"
            >
                <x-slot:icon>
                    <x-icon name="drone" class="size-4" />
                </x-slot:icon>
                Flight Controllers
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'ShieldController']"
            >
                <x-slot:icon>
                    <x-icon name="shield-user" class="size-4" />
                </x-slot:icon>
                Shield Controllers
            </x-app.sidemenu-item>

            <div class="divider m-0"></div>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'JumpDrive']"
            >
                <x-slot:icon>
                    <x-icon name="egg-fried" class="size-4" />
                </x-slot:icon>
                Jump Drives
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'Radar']"
            >
                <x-slot:icon>
                    <x-icon name="wifi" class="size-4" />
                </x-slot:icon>
                Radars
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'SelfDestruct']"
            >
                <x-slot:icon>
                    <x-icon name="octagon-alert" class="size-4" />
                </x-slot:icon>
                Self-Destructs
            </x-app.sidemenu-item>

            <div class="divider m-0"></div>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'WeaponDefensive']"
            >
                <x-slot:icon>
                    <x-icon name="activity" class="size-4" />
                </x-slot:icon>
                Countermeasures
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'WeaponGun']"
            >
                <x-slot:icon>
                    <x-icon name="crosshair" class="size-4" />
                </x-slot:icon>
                Hardpoint Weapons
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'MissileLauncher']"
            >
                <x-slot:icon>
                    <x-icon name="square-stack" class="size-4" />
                </x-slot:icon>
                Missile Racks
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'Turret']"
            >
                <x-slot:icon>
                    <x-icon name="circle-plus" class="size-4" />
                </x-slot:icon>
                Turrets & Gimbals
            </x-app.sidemenu-item>

            <div class="divider m-0"></div>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'Bomb']"
            >
                <x-slot:icon>
                    <x-icon name="bomb" class="size-4" />
                </x-slot:icon>
                Bombs
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'Missile']"
            >
                <x-slot:icon>
                    <x-icon name="arrow-up" class="size-4" />
                </x-slot:icon>
                Missiles
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'EMP']"
            >
                <x-slot:icon>
                    <x-icon name="zap" class="size-4" />
                </x-slot:icon>
                EMP
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'QuantumInterdictionGenerator']"
            >
                <x-slot:icon>
                    <x-icon name="mouse-pointer-2-off" class="size-4" />
                </x-slot:icon>
                QED
            </x-app.sidemenu-item>

            <div class="divider m-0"></div>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'MiningLaser']"
            >
                <x-slot:icon>
                    <x-icon name="target" class="size-4" />
                </x-slot:icon>
                Mining Lasers
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'MiningModifier']"
            >
                <x-slot:icon>
                    <x-icon name="cog" class="size-4" />
                </x-slot:icon>
                Mining Modifiers
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'SalvageModifier']"
            >
                <x-slot:icon>
                    <x-icon name="refresh-cw" class="size-4" />
                </x-slot:icon>
                Salvage Modifiers
            </x-app.sidemenu-item>

            <x-app.sidemenu-item
                :route="'web.vehicle-items.type'"
                :params="['type' => 'TractorBeam']"
            >
                <x-slot:icon>
                    <x-icon name="magnet" class="size-4" />
                </x-slot:icon>
                Tractor Beams
            </x-app.sidemenu-item>



        </x-slot:children>
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
