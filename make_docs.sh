#!/bin/bash

# Comm Links
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkController --output-file=docs/comm_links/comm_links.md
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController --output-file=docs/comm_links/comm_links_search.md

# Manufacturer
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController --output-file=docs/manufacturers/manufacturers.md

# Starmap
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\StarCitizen\Starmap\Starsystem\StarsystemController --output-file=docs/starmap/starsystems.md

# Stats
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\StarCitizen\Stat\StatController --output-file=docs/stats/stats.md

# Vehicles
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController --output-file=docs/vehicles/ground_vehicles.md
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController --output-file=docs/vehicles/ships.md

# Galactapedia
php artisan api:docs --use-controller=App\Http\Controllers\Api\V1\StarCitizen\Galactapedia\GalactapediaController --output-file=docs/galactapedia/galactapedia.md

# Generate Playground
# php artisan api:docs --output=api.md
# snowboard html api.md -o ./docs/playground
#rm api.md